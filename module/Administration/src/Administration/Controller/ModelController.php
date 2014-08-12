<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Code\Scanner\DirectoryScanner;


class ModelController extends AbstractActionController
{
    protected $errors;

    public function indexAction()
    {
        $requested_model = $this->params()->fromRoute('model');
        if (!array_key_exists($requested_model, $this->getAvailableModels())) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        try {
            $model = $this->initialiseModel($requested_model);
        }
        catch (\Exception $ex) {
            echo 'Catch Top';
        }

        return new ViewModel();
    }

    public function addAction()
    {
        return new ViewModel();
    }

    public function editAction()
    {
        return new ViewModel();
    }

    public function saveAction()
    {
        return new ViewModel();
    }

    public function deleteAction()
    {
        return true;
    }

    public function deleteMultipleAction()
    {
        return true;
    }

    private function initialiseModel($model)
    {
        $modelsArray          = $this->getAvailableModels();
        $modelDefinitionArray = require($modelsArray[$model]);

        try {
            $this->validateModel($modelDefinitionArray);
        }
        catch (\Exception $ex) {
            $this->errors[] = 'Could not instantiate model';
            return false;
        }



    }

    /**
     * Validates model config array
     * @param $model = configuration array
     * @throws \Exception
     */
    private function validateModel($model)
    {
        $mandatorySettings = array(
            'model_name',
            'table_name',
            'prefix',
        );

        foreach ($mandatorySettings as $setting) {
            if (!array_key_exists($setting, $model) || empty($model[$setting])) {
                $error = 'Missing "'. $setting . '" definition in model config ';
                $this->errors[] = $error;
                throw new \Exception($error);
            }
        }
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
