<?php
namespace Administration\Helper;

use Administration\Helper\DbGateway\ExchangeArrayObject;
use Administration\Helper\DbGateway\CmsTableGateway;
use Administration\Helper\DbGateway\ModelTable;
use Administration\Helper\DbGateway\TranslationTable;
use Administration\Helper\Manager\ModelManager;
use Administration\Helper\Manager\RelationManager;
use Administration\Helper\Manager\TranslationManager;
use Administration\Helper\Validator\ModelValidator;
use Zend\Code\Scanner\DirectoryScanner;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;

class ModelHandler
{
    private $errors               = array();
    private $initialised          = false;
    private $availableModelsArray = array();
    private $modelManager;
    private $translationManager;
    private $relationManagers  = array();

    protected $errorMsgArray = array(
        'ERROR_1' => 'The requested Model does not exist!',
        'ERROR_2' => 'Model is missing definitions array!',
        'ERROR_3' => 'The requested Relation Model does not exist!',
        'ERROR_4' => 'Relation Model is missing definitions array!',

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

    public function getTranslationTable()
    {
        return $this->translationTable;
    }

    private function initialiseMainTable()
    {
        $gateway = $this->initialiseTableGateway($this->modelManager->getTableName(),$this->modelManager->getTableExchangeArray());
        return new ModelTable($gateway, $this->modelManager->getTableColumnsDefinition(), $this->modelManager->getModelDbTableSync());
    }

    private function initialiseTranslationTable()
    {
        if ($this->translationManager->requiresTable()) {
            $gateway = $this->initialiseTableGateway($this->translationManager->getTableName(),$this->translationManager->getTableExchangeArray());
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

                if ($relationManager->requiresTable()) {
                    $gateway = $this->initialiseTableGateway($relationManager->getTableName(),$relationManager->getTableExchangeArray());
                    $this->relationManagers[$name]['table'] = new ModelTable($gateway, $relationManager->getTableColumnsDefinition(), $this->modelManager->getModelDbTableSync());
                } else {
                    $this->relationManagers[$name]['table'] = false;
                }

                if ($relationManager->requiresColumn()) {
                    //add the relation column to the main table
                    $this->modelManager->setRelationField($relationManager->getColumn());
                }
            }
        }
        return false;
    }

    private function initialiseTableGateway($tableName, $tableFields)
    {
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype( new ExchangeArrayObject($tableFields));
        return new CmsTableGateway($tableName, $this->adapter, null, $resultSetPrototype);
    }

    private function modelExists($model)
    {
        $modelsArray = $this->getAvailableModels();
        if (array_key_exists($model, $modelsArray)) {
            return true;
        } else {
            return false;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function isInitialised()
    {
        return $this->initialised;
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

    public function getModelManager()
    {
        return $this->modelManager;
    }
}