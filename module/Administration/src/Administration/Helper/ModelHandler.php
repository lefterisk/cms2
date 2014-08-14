<?php
namespace Administration\Helper;

use Administration\Helper\Validator\ModelValidator;
use Zend\Code\Scanner\DirectoryScanner;

class ModelHandler
{
    protected $errors      = array();
    protected $initialised = false;
    protected $availableModelsArray = array();

    public function __construct($model)
    {
        $modelsArray = $this->getAvailableModels();
        if (array_key_exists($model, $modelsArray)) {
            $modelDefinitionArray = require($modelsArray[$model]);
        } else {
            $this->errors[] = 'The requested Model does not exist!';
            return;
        }

        if (!is_array($modelDefinitionArray)) {
            $this->errors[] = 'Model is missing definitions array!';
            return;
        }

        $modelValidator = new ModelValidator($modelDefinitionArray);

        if (!$modelValidator->validate()) {
            $this->errors = array_merge($this->errors, $modelValidator->getErrors());
            return;
        }

        $this->initialised = true;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function isInitialised()
    {
        return $this->initialised;
    }

    /**
     * Scans the models directory
     * @return array of available models
     */
    private function getAvailableModels()
    {
        if (empty($this->availableModelsArray)) {
            $scanner = new DirectoryScanner(__DIR__ . '/../Model/');
            $this->availableModelsArray = array();
            if (is_array($scanner->getFiles())) {
                foreach ($scanner->getFiles() as $model) {
                    $file = explode('/', $model);
                    $this->availableModelsArray[str_replace('.php', '', array_pop($file))] = $model;
                }
            }
        }
        return $this->availableModelsArray;
    }
}