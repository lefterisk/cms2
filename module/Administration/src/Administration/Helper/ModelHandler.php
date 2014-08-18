<?php
namespace Administration\Helper;

use Administration\Helper\DbGateway\ExchangeArrayObject;
use Administration\Helper\DbGateway\CmsTableGateway;
use Administration\Helper\DbGateway\ModelTable;
use Administration\Helper\Manager\ModelManager;
use Administration\Helper\Validator\ModelValidator;
use Zend\Code\Scanner\DirectoryScanner;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;

class ModelHandler
{
    private $errors               = array();
    private $initialised          = false;
    private $availableModelsArray = array();
    private $model;

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
        $this->model   = new ModelManager($modelDefinitionArray);

        $this->initialiseMainTable();
        $this->initialiseTranslationTable();

        //var_dump($this->mainTable->fetchAll());

        $this->initialised = true;
    }

    private function initialiseMainTable()
    {
        $gateway = $this->initialiseTableGateway($this->model->getModelName(),$this->model->getMainTableExchangeArrayFields());
        $this->mainTable = new ModelTable($gateway, $this->model->getMainTableColumns(), $this->model->getModelDbTableSync());
    }

    private function initialiseTranslationTable()
    {
        var_dump();
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