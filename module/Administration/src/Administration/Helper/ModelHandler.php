<?php
namespace Administration\Helper;

use Administration\Helper\DbGateway\CmsTableGateway;
use Administration\Helper\DbGateway\ModelTable;
use Administration\Helper\DbGateway\ParentLookupTable;
use Administration\Helper\DbGateway\TranslationTable;
use Administration\Helper\Manager\ModelManager;
use Administration\Helper\Manager\ParentManager;
use Administration\Helper\Manager\TranslationManager;
use Administration\Helper\Validator\ModelValidator;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Filter\Int;

class ModelHandler
{
    private $errors                  = array();
    private $initialised             = false;
    private $parentFieldName         = 'parent_id';
    private $modelManager;
    private $parentManager;
    private $translationManager;
    private $relationHandlers        = array();
    private $customSelectionHandlers = array();
    private $modelHelper;
    private $actionManagerHandler;

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
        $this->modelHelper    = new ModelHelper();
        $this->adapter        = $dbAdapter;
        $modelDefinitionArray = $this->modelChecks($model, 'main');
        if (!$modelDefinitionArray) {
            return;
        }
        $this->modelManager         = new ModelManager($modelDefinitionArray);
        $this->actionManagerHandler = new ActionManagerHandler($this->getModelManager()->getActionManagers());

        $this->parentManager        = new ParentManager($this->modelManager);
        $this->parentTable          = $this->initialiseModelParentLookupTable();

        $this->translationManager   = new TranslationManager($this->modelManager);
        $this->translationTable     = $this->initialiseTranslationTable();

        $this->initialiseRelationsTables();
        $this->initialiseCustomSelectionTables();

        //main table is initialised last so we have the relation & custom selection columns
        $this->modelTable = $this->initialiseMainTable();

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

    public function getParentManager()
    {
        return $this->parentManager;
    }

    public function getParentTable()
    {
        return $this->parentTable;
    }

    public function getTranslationManager()
    {
        return $this->translationManager;
    }

    public function getRelationHandlers()
    {
        return $this->relationHandlers;
    }

    public function getCustomSelectionHandlers()
    {
        return $this->customSelectionHandlers;
    }

    public function getActionManagerHandler()
    {
        return $this->actionManagerHandler;
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

    private function initialiseModelParentLookupTable()
    {
        $gateway =  new CmsTableGateway($this->parentManager->getTableName(), $this->adapter);
        return new ParentLookupTable($gateway, $this->parentManager->getTableColumnsDefinition(), $this->modelManager->getModelDbTableSync());
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
        foreach ($this->modelManager->getRelations() as $relation) {
            $relatedModelDefinition = $this->modelChecks($relation['related_model'], 'relation');
            if ($relatedModelDefinition) {
                $relationHandler = new RelationHandler($relation, $relatedModelDefinition, $this->getModelManager(), $this->adapter);
                $this->relationHandlers[$relationHandler->getRelationManager()->getFieldName()] = $relationHandler;
            }
        }
        $this->setRelationFieldsToMainModel();
    }

    private function setRelationFieldsToMainModel()
    {
        foreach($this->relationHandlers as $relationHandler) {
            if ($relationHandler instanceof RelationHandler && $relationHandler->getRelationManager()->requiresColumn()) {
                $this->modelManager->setRelationField($relationHandler->getRelationManager()->getColumn());
            }
        }
    }

    private function initialiseCustomSelectionTables()
    {
        foreach ($this->modelManager->getCustomSelections() as $customSelection) {
            $customSelectionHandler = new CustomSelectionHandler($customSelection, $this->getModelManager(), $this->adapter);
            $this->customSelectionHandlers[$customSelectionHandler->getCustomSelectionManager()->getFieldName()] = $customSelectionHandler;
        }
        $this->setCustomSelectionFieldsToMainModel();
    }

    private function setCustomSelectionFieldsToMainModel()
    {
        foreach($this->customSelectionHandlers as $customSelectionHandler) {
            if ($customSelectionHandler instanceof CustomSelectionHandler && $customSelectionHandler->getCustomSelectionManager()->requiresColumn()) {
                $this->modelManager->setCustomSelectionField($customSelectionHandler->getCustomSelectionManager()->getColumn());
            }
        }
    }

//    private function initialiseTableGateway($tableName, $tableFields)
//    {
//        $resultSetPrototype = new ResultSet();
//        $resultSetPrototype->setArrayObjectPrototype( new ExchangeArrayObject($tableFields));
//        return new CmsTableGateway($tableName, $this->adapter, null, null);
//    }

    private function modelChecks($model, $type = 'main')
    {
        if ($this->modelHelper->modelExists($model)) {
            $models = $this->modelHelper->getAvailableModels();
            $modelDefinitionArray = require($models[$model]);
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

    public function getRelationFieldsForMainTable()
    {
        $fieldNames = array();
        foreach($this->getRelationHandlers() as $relationHandler) {
            if ($relationHandler instanceof RelationHandler && $relationHandler->getRelationManager()->requiresColumn()) {
                $fieldNames[] = $relationHandler->getRelationManager()->getFieldName();
            }
        }
        return $fieldNames;
    }

    public function getRelationFieldsForRelationTables()
    {
        $fieldNames = array();
        foreach($this->getRelationHandlers() as $relationHandler) {
            if ($relationHandler instanceof RelationHandler && $relationHandler->getRelationManager()->requiresTable()) {
                $fieldNames[$relationHandler->getRelationManager()->getTableName()] = $relationHandler->getRelationManager()->getFieldName();
            }
        }
        return $fieldNames;
    }

    public function getRelationFieldsNames()
    {
        return array_merge($this->getRelationFieldsForMainTable(), $this->getRelationFieldsForRelationTables());
    }

    public function getCustomSelectionFieldsForMainTable()
    {
        $fieldNames = array();
        foreach($this->getCustomSelectionHandlers() as $customSelectionHandler) {
            if ($customSelectionHandler instanceof CustomSelectionHandler && $customSelectionHandler->getCustomSelectionManager()->requiresColumn()) {
                $fieldNames[] = $customSelectionHandler->getCustomSelectionManager()->getFieldName();
            }
        }
        return $fieldNames;
    }

    public function getCustomSelectionFieldsForCustomSelectionTables()
    {
        $fieldNames = array();
        foreach($this->getCustomSelectionHandlers() as $customSelectionHandler) {
            if ($customSelectionHandler instanceof CustomSelectionHandler && $customSelectionHandler->getCustomSelectionManager()->requiresTable()) {
                $fieldNames[$customSelectionHandler->getCustomSelectionManager()->getTableName()] = $customSelectionHandler->getCustomSelectionManager()->getFieldName();
            }
        }
        return $fieldNames;
    }

    public function getCustomSelectionFieldsNames()
    {
        return array_merge($this->getCustomSelectionFieldsForMainTable(), $this->getCustomSelectionFieldsForCustomSelectionTables());
    }

    public function getParentFieldName()
    {
        return $this->parentFieldName;
    }

    public function getOverAllInputFilter()
    {
        //main table input-filter
        $inputFilter = $this->modelManager->getInputFilter();
        //relations input-filters
        foreach($this->getRelationHandlers() as $relationHandler) {
            if ($relationHandler instanceof RelationHandler) {
                $inputFilter = $relationHandler->getRelationManager()->getInputFilter($inputFilter);
            }
        }

        //custom selection input-filters
        foreach($this->getCustomSelectionHandlers() as $customSelectionHandler) {
            if ($customSelectionHandler instanceof CustomSelectionHandler) {
                $inputFilter = $customSelectionHandler->getCustomSelectionManager()->getInputFilter($inputFilter);
            }
        }
        return $inputFilter;
    }

    public function save(Array $data)
    {
        $data                    = $this->getActionManagerHandler()->getActionProcessedData('preSave', $data);
        $mainTableFields         = array();
        $translationTableFields  = array();
        $relationTablesFields    = array();
        $parentTableField        = array();
        $relationsTablesToFields = $this->getRelationFieldsForRelationTables();
        $customSelectionTablesFields   = array();
        $customSelectionTablesToFields = $this->getCustomSelectionFieldsForCustomSelectionTables();

        foreach ($data as $fieldName => $fieldValue) {
            if (in_array( $fieldName, array_merge($this->modelManager->getAllNonMultilingualFields(),$this->getRelationFieldsForMainTable(),$this->getCustomSelectionFieldsForMainTable()))) {
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
            if (in_array($fieldName, $relationsTablesToFields)) {
                foreach ($relationsTablesToFields as $table => $field) {
                    if ($field == $fieldName) {
                        $relationTablesFields[$table] = array('field' => $fieldName, 'field_values' => $fieldValue);
                    }
                }
            }
            if (in_array($fieldName, $customSelectionTablesToFields)) {
                foreach ($customSelectionTablesToFields as $table => $field) {
                    if ($field == $fieldName) {
                        $customSelectionTablesFields[$table] = array('field' => $fieldName, 'field_values' => $fieldValue);
                    }
                }
            }
            if ($fieldName == $this->parentFieldName) {
                $parentTableField = $fieldValue;
            }
        }

        if (isset($data['id']) && !empty($data['id'])) {
            $this->getModelTable()->save($data['id'], $mainTableFields);
            if ($this->modelManager->getMaximumTreeDepth() > 0) {
                $this->parentTable->save($data['id'], $this->getModelManager()->getPrefix() . 'id', $this->parentFieldName, $parentTableField);
            }
            foreach ($translationTableFields as $languageId => $fields) {
                $this->getTranslationTable()->save($fields, array($this->getModelManager()->getPrefix() . 'id' => $data['id'], 'language_id' => $languageId));
            }
            $this->saveRelationTables($relationTablesFields, $data['id']);
            $this->saveCustomSelectionTables($customSelectionTablesFields, $data['id']);
        } else {
            $this->getModelTable()->save(null, $mainTableFields);
            foreach ($translationTableFields as $languageId => $fields) {
                $fields = array_merge(
                    $fields,
                    array(
                        $this->getModelManager()->getPrefix() . 'id' =>  $this->getModelTable()->getLastInsertValue(),
                        'language_id' => $languageId
                    )
                );
                $this->getTranslationTable()->save($fields);
            }
            $this->saveRelationTables($relationTablesFields, $this->getModelTable()->getLastInsertValue());
            $this->saveCustomSelectionTables($customSelectionTablesFields, $this->getModelTable()->getLastInsertValue());
        }
        $this->getActionManagerHandler()->getActionProcessedData('postSave', $data);
    }

    protected function saveRelationTables(Array $tableFields, $id)
    {
        $relationHandlers = $this->getRelationHandlers();
        foreach ($tableFields as $field) {
            if (array_key_exists($field['field'], $relationHandlers)) {
                $relationHandler = $relationHandlers[$field['field']];
                if ($relationHandler instanceof RelationHandler && $relationHandler->getRelationManager()->requiresTable()) {
                    $relationHandler->getRelationTable()->save($id, $this->getModelManager()->getPrefix() . 'id', $field['field'], $field['field_values']);
                }
            }
        }
    }

    protected function saveCustomSelectionTables(Array $tableFields, $id)
    {
        $customSelectionHandlers = $this->getCustomSelectionHandlers();
        foreach ($tableFields as $field) {
            if (array_key_exists($field['field'], $customSelectionHandlers)) {
                $customSelectionHandler = $customSelectionHandlers[$field['field']];
                if ($customSelectionHandler instanceof CustomSelectionHandler && $customSelectionHandler->getCustomSelectionManager()->requiresTable()) {
                    $customSelectionHandler->getCustomSelectionTable()->save($id, $this->getModelManager()->getPrefix() . 'id', $field['field'], $field['field_values']);
                }
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

        $id = $this->getActionManagerHandler()->getActionProcessedData('preSelect', $id);

        $mainTableData = $this->getModelTable()->getTableGateway()->select(array('id' => $id))->current();
        if (!$mainTableData) {
            $this->errors[] = $this->errorMsgArray['ERROR_6'];
            throw new \Exception();
        }

        $translationData = array();
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

        $relationData = array();
        foreach($this->getRelationHandlers() as $relationHandler) {
            if ($relationHandler instanceof RelationHandler && $relationHandler->getRelationManager()->requiresTable()) {
                $results = $relationHandler->getRelationTable()->getTableGateway()->select(array($this->modelManager->getPrefix() . 'id' => $id));
                foreach ($results as $result) {
                    $relationData[$relationHandler->getRelationManager()->getFieldName()][] = $result->{$relationHandler->getRelationManager()->getFieldName()};
                }
            }
        }

        $customSelectionData = array();
        foreach($this->getCustomSelectionHandlers() as $customSelectionHandler) {
            if ($customSelectionHandler instanceof CustomSelectionHandler && $customSelectionHandler->getCustomSelectionManager()->requiresTable()) {
                $results = $customSelectionHandler->getCustomSelectionTable()->getTableGateway()->select(array($this->modelManager->getPrefix() . 'id' => $id));
                foreach ($results as $result) {
                    $customSelectionData[$customSelectionHandler->getCustomSelectionManager()->getFieldName()][] = $result->{$customSelectionHandler->getCustomSelectionManager()->getFieldName()};
                }
            }
        }

        $parentFieldFrame = array();
        if ($this->modelManager->getMaximumTreeDepth() > 0 && $this->getParentManager()->requiresTable()) {
            $parentData = $this->parentTable->getTableGateway()->select(array($this->modelManager->getPrefix() . 'id' => $id));
            foreach ($parentData as $result) {
                $parentFieldFrame[$this->getParentFieldName()][] = $result->{$this->getParentFieldName()};
            }
        }
        return $this->getActionManagerHandler()->getActionProcessedData('postSelect', array_merge($mainTableData->getArrayCopy(),$translationData,$relationData,$customSelectionData,$parentFieldFrame));
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

        $id = $this->getActionManagerHandler()->getActionProcessedData('preDelete', $id);

        try {
            if ($hard) {
                $rowsAffected = $this->getModelTable()->getTableGateway()->delete(array('id' => $id));
                if ($rowsAffected > 0 && $this->modelManager->isMultiLingual()) {
                    $this->getTranslationTable()->getTableGateway()->delete(array($this->getModelManager()->getPrefix() . 'id' => $id));
                }
                foreach($this->getRelationHandlers() as $relationHandler) {
                    if ($relationHandler instanceof RelationHandler && $relationHandler->getRelationManager()->requiresTable()) {
                        $relationHandler->getRelationTable()->delete($id, $this->modelManager->getPrefix() . 'id');
                    }
                }
                foreach($this->getCustomSelectionHandlers() as $customSelectionHandler) {
                    if ($customSelectionHandler instanceof CustomSelectionHandler && $customSelectionHandler->getCustomSelectionManager()->requiresTable()) {
                        $customSelectionHandler->getCustomSelectionTable()->delete($id, $this->modelManager->getPrefix() . 'id');
                    }
                }
                $this->getActionManagerHandler()->getActionProcessedData('postDelete', $id);
            } else {
                //todo handle soft delete
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