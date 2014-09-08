<?php
namespace Administration\Helper;

use Administration\Helper\DbGateway\CmsTableGateway;
use Administration\Helper\DbGateway\ModelTable;
use Administration\Helper\DbGateway\TranslationTable;
use Administration\Helper\Manager\ModelManager;
use Administration\Helper\Manager\RelationManager;
use Administration\Helper\Manager\TranslationManager;
use Administration\Helper\Validator\ModelValidator;
use Zend\Code\Scanner\DirectoryScanner;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Filter\Int;

class ModelHandler
{
    private $errors               = array();
    private $initialised          = false;
    private $availableModelsArray = array();
    private $modelManager;
    private $translationManager;
    private $relationManagers  = array();

    protected $errorMsgArray = array(
        'ERROR_1'  => 'The requested Model does not exist!',
        'ERROR_2'  => 'Model is missing definitions array!',
        'ERROR_3'  => 'The requested Relation Model does not exist!',
        'ERROR_4'  => 'Relation Model is missing definitions array!',
        'ERROR_5'  => 'When requesting an item by "id", "id" must be an integer!',
        'ERROR_6'  => 'You requested an item that does not exist!',
        'ERROR_7'  => 'When deleting an item a valid integer "id" must be provided!',
        'ERROR_8'  => 'When deleting an item, "hard" parameter if provided must be a boolean!',
        'ERROR_9'  => 'Something went wrong while trying to delete the requested item(s)!',
        'ERROR_10' => 'When requesting deletion of multiple items you have to provide an array of ids to delete!',
    );

    public function __construct($model, AdapterInterface $dbAdapter)
    {
        $modelDefinitionArray = $this->modelChecks($model, 'main');
        if (!$modelDefinitionArray) {
            return;
        }
        $this->adapter = $dbAdapter;
        
        $this->modelManager = new ModelManager($modelDefinitionArray);

        $this->translationManager = new TranslationManager($this->modelManager);
        $this->translationTable   = $this->initialiseTranslationTable();

        $this->initialiseRelationsTables();

        //main table is initialised last so we have the relation & custom selection columns
        $this->modelTable   = $this->initialiseMainTable();

        if (count($this->getErrors()) == 0) {
            $this->initialised = true;
        }
    }

    public function getModelTable()
    {
        return $this->modelTable;
    }

    public function getModelManager()
    {
        return $this->modelManager;
    }

    public function getTranslationTable()
    {
        return $this->translationTable;
    }

    public function getTranslationManager()
    {
        return $this->translationManager;
    }

    public function getRelationManagers()
    {
        return $this->relationManagers;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function isInitialised()
    {
        return $this->initialised;
    }

    private function initialiseMainTable()
    {
        $gateway =  new CmsTableGateway($this->modelManager->getTableName(), $this->adapter);
        return new ModelTable($gateway, $this->modelManager->getTableColumnsDefinition(), $this->modelManager->getModelDbTableSync());
    }

    private function initialiseTranslationTable()
    {
        if ($this->translationManager->requiresTable()) {
            $gateway =  new CmsTableGateway($this->translationManager->getTableName(), $this->adapter);
            return new TranslationTable($gateway, $this->translationManager->getTableColumnsDefinition(), $this->modelManager->getModelDbTableSync());
        } else {
            return false;
        }
    }

    private function initialiseRelationsTables()
    {
        foreach ($this->modelManager->getRelations() as  $name => $relation) {
            $relatedModelDefinition = $this->modelChecks($relation['related_model'], 'relation');
            if ($relatedModelDefinition) {
                $relatedModelManager = new ModelManager($relatedModelDefinition);
                $relationManager     = new RelationManager($relation, $this->modelManager->getPrefix(),$relatedModelManager->getPrefix());
                $this->relationManagers[$name]['manager'] = $relationManager;
                $this->relationManagers[$name]['related_model_table'] = new ModelTable(
                    new CmsTableGateway(
                        $relation['related_model'],
                        $this->adapter
                    ),
                    $relationManager->getTableColumnsDefinition(), $this->modelManager->getModelDbTableSync()
                );

                if ($relationManager->requiresTable()) {
                    $gateway = new CmsTableGateway($relationManager->getTableName(), $this->adapter);
                    $this->relationManagers[$name]['relation_table'] = new ModelTable($gateway, $relationManager->getTableColumnsDefinition(), $this->modelManager->getModelDbTableSync());
                }

                if ($relationManager->requiresColumn()) {
                    //add the relation column to the main table
                    $this->modelManager->setRelationField($relationManager->getColumn());
                }
            }
        }
        return false;
    }

//    private function initialiseTableGateway($tableName, $tableFields)
//    {
//        $resultSetPrototype = new ResultSet();
//        $resultSetPrototype->setArrayObjectPrototype( new ExchangeArrayObject($tableFields));
//        return new CmsTableGateway($tableName, $this->adapter, null, null);
//    }

    private function modelExists($model)
    {
        $modelsArray = $this->getAvailableModels();
        if (array_key_exists($model, $modelsArray)) {
            return true;
        } else {
            return false;
        }
    }

    private function getAvailableModels()
    {
        if (empty($this->availableModelsArray)) {
            $scanner = new DirectoryScanner(__DIR__ . '/../Model/');
            $this->availableModelsArray = array();
            if (is_array($scanner->getFiles())) {
                foreach ($scanner->getFiles() as $model) {
                    //Windows filesystem returns paths with forward slash
                    $file = explode('/', str_replace('\\','/',$model));
                    $this->availableModelsArray[str_replace('.php', '', array_pop($file))] = $model;
                }
            }
        }
        return $this->availableModelsArray;
    }

    private function modelChecks($model, $type = 'main')
    {
        if ($this->modelExists($model)) {
            $modelDefinitionArray = require($this->availableModelsArray[$model]);
        } else {
            switch ($type) {
                case 'main':
                    $this->errors[] = $this->errorMsgArray['ERROR_1'];
                    break;
                case 'relation':
                    $this->errors[] = $this->errorMsgArray['ERROR_3'];
                    break;
            }
            return false;
        }

        if (!is_array($modelDefinitionArray)) {
            switch ($type) {
                case 'main':
                    $this->errors[] = $this->errorMsgArray['ERROR_2'];
                    break;
                case 'relation':
                    $this->errors[] = $this->errorMsgArray['ERROR_4'];
                    break;
            }
            return false;
        }

        $modelValidator = new ModelValidator($modelDefinitionArray);
        if (!$modelValidator->validate()) {
            $this->errors = array_merge($this->errors, $modelValidator->getErrors());
            return false;
        } else {
            return $modelDefinitionArray;
        }
    }

    public function getRelationFieldsNames()
    {
        $fieldNames = array();
        foreach($this->getRelationManagers() as $relation) {
            $relationManager = $relation['manager'];
            if ($relationManager instanceof RelationManager) {
                $fieldNames[] = $relationManager->getFieldName();
            }
        }
        return $fieldNames;
    }

    public function getOverAllInputFilter()
    {
        //main table inputfilter
        $inputFilter = $this->modelManager->getInputFilter();
        //relations inputfilters
        foreach($this->getRelationManagers() as $relation) {
            $relationManager = $relation['manager'];
            if ($relationManager instanceof RelationManager) {
                $inputFilter = $relationManager->getInputFilter($inputFilter);
            }
        }
        return $inputFilter;
    }

    public function save(Array $data)
    {
        $mainTableFields        = array();
        $translationTableFields = array();

        foreach ($data as $fieldName => $fieldValue) {
            if (in_array( $fieldName, array_merge($this->modelManager->getAllNonMultilingualFields(),$this->getRelationFieldsNames()))) {
                $mainTableFields[$fieldName] = $fieldValue;
            }
            if (preg_match('/\[/', $fieldName)) {
                $actualNameParts = explode('[', $fieldName);
                $actualName      = $actualNameParts[0];

                if (in_array($actualName, $this->modelManager->getAllMultilingualFields())) {
                    $languageId = explode(']', $actualNameParts[1]);
                    $languageId = $languageId[0];
                    $translationTableFields[$languageId][$actualName] = $fieldValue;
                }
            }
        }
        if (isset($data['id']) && !empty($data['id'])) {
            $this->getModelTable()->getTableGateway()->update($mainTableFields, array('id' => $data['id']));
            foreach ($translationTableFields as $languageId => $fields) {
                $this->getTranslationTable()->getTableGateway()->update($fields, array($this->getModelManager()->getPrefix() . 'id' => $data['id'], 'language_id' => $languageId));
            }
        } else {
            $this->getModelTable()->getTableGateway()->insert($mainTableFields);
            foreach ($translationTableFields as $languageId => $fields) {
                $fields = array_merge(
                    $fields,
                    array(
                        $this->getModelManager()->getPrefix() . 'id' =>  $this->getModelTable()->getTableGateway()->getLastInsertValue(),
                        'language_id' => $languageId
                    )
                );
                $this->getTranslationTable()->getTableGateway()->insert($fields);
            }
        }
    }

    public function getItemById($id)
    {
        $filter   = new Int();
        if (empty($id) || !is_int( $filter->filter($id))) {
            $this->errors[] = $this->errorMsgArray['ERROR_5'];
            throw new \Exception();
        }

        $mainTableData           = $this->getModelTable()->getTableGateway()->select(array('id' => $id))->current();
        if (!$mainTableData) {
            $this->errors[] = $this->errorMsgArray['ERROR_6'];
            throw new \Exception();
        }
        $translationData         = array();
        if ($this->modelManager->isMultiLingual()) {
            $rawTranslationTableData = $this->getTranslationTable()->getTableGateway()->select(array($this->getModelManager()->getPrefix() . 'id' => $id));
            foreach ($rawTranslationTableData as $translation) {
                foreach ($translation as $field => $value) {
                    if (!in_array($field, array($this->getModelManager()->getPrefix() . 'id', 'language_id'))) {
                        $translationData[$field][$translation['language_id']] = $value;
                    }
                }
            }
        }

        return array_merge($mainTableData->getArrayCopy(),$translationData);
    }

    public function deleteItemById($id, $hard = true)
    {
        $filter   = new Int();
        if (empty($id) || !is_int( $filter->filter($id))) {
            $this->errors[] = $this->errorMsgArray['ERROR_7'];
            throw new \Exception();
        }

        if (!is_bool($hard)) {
            $this->errors[] = $this->errorMsgArray['ERROR_8'];
            throw new \Exception();
        }

        try {
            if ($hard) {
                $rowsAffected = $this->getModelTable()->getTableGateway()->delete(array('id' => $id));
                if ($rowsAffected > 0 && $this->modelManager->isMultiLingual()) {
                    $this->getTranslationTable()->getTableGateway()->delete(array($this->getModelManager()->getPrefix() . 'id' => $id));
                }
            } else {

            }
        } catch (\Exception $ex) {
            $this->errors[] = $this->errorMsgArray['ERROR_9'];
            throw new \Exception();
        }
    }

    public function deleteMultipleItemsById($idsToDeleteArray, $hard = true)
    {
        $successfulDeletes = 0;
        $failedDeletes     = 0;

        if (!is_array($idsToDeleteArray) || empty($idsToDeleteArray)) {
            $this->errors[] = $this->errorMsgArray['ERROR_10'];
            throw new \Exception();
        }

        if (!is_bool($hard)) {
            $this->errors[] = $this->errorMsgArray['ERROR_8'];
            throw new \Exception();
        }

        foreach ($idsToDeleteArray as $id) {
            try {
                $this->deleteItemById($id, $hard);
                $successfulDeletes++;
            } catch (\Exception $ex) {
                $failedDeletes++;
            }
        }
        return array(
            'successfulDeletes' => $successfulDeletes,
            'failedDeletes'     => $failedDeletes
        );
    }
}