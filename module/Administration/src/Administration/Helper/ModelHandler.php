<?php
namespace Administration\Helper;

use Administration\Helper\DbGateway\ExchangeArrayObject;
use Administration\Helper\DbGateway\CmsTableGateway;
use Administration\Helper\DbGateway\ModelTable;
use Administration\Helper\DbGateway\TranslationTable;
use Administration\Helper\Manager\ModelManager;
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

    protected $errorMsgArray = array(
        'ERROR_1' => 'The requested Model does not exist!',
        'ERROR_2' => 'Model is missing definitions array!',
    );

    public function __construct($model, AdapterInterface $dbAdapter)
    {
        if ($this->modelExists($model)) {
            $modelDefinitionArray = require($this->availableModelsArray[$model]);
        } else {
            $this->errors[] = $this->errorMsgArray['ERROR_1'];
            return;
        }

        if (!is_array($modelDefinitionArray)) {
            $this->errors[] = $this->errorMsgArray['ERROR_2'];
            return;
        }

        $modelValidator = new ModelValidator($modelDefinitionArray);

        if (!$modelValidator->validate()) {
            $this->errors = array_merge($this->errors, $modelValidator->getErrors());
            return;
        }
        $this->adapter = $dbAdapter;
        
        $this->modelManager   = new ModelManager($modelDefinitionArray);
        $this->initialiseMainTable();

        $this->translationManager = new TranslationManager($this->modelManager);
        $this->initialiseTranslationTable();

        $this->initialised = true;
    }

    private function initialiseMainTable()
    {
        $gateway = $this->initialiseTableGateway($this->modelManager->getTableName(),$this->modelManager->getTableExchangeArray());
        $this->modelTable = new ModelTable($gateway, $this->modelManager->getTableColumnsDefinition(), $this->modelManager->getModelDbTableSync());
    }

    private function initialiseTranslationTable()
    {
        if ($this->translationManager->requiresTable()) {
            $gateway = $this->initialiseTableGateway($this->translationManager->getTableName(),$this->translationManager->getTableExchangeArray());
            $this->translationTable = new TranslationTable($gateway, $this->translationManager->getTableColumnsDefinition(), $this->modelManager->getModelDbTableSync());
        } else {
            $this->translationTable = false;
        }
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
}