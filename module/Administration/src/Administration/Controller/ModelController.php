<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Administration\Controller;

use Administration\Helper\FormHandler;
use Administration\Helper\ModelHandler;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class ModelController extends AbstractActionController
{
    protected $errors = array();

    public function indexAction()
    {
        $requested_model = $this->params()->fromRoute('model');
        $model = new ModelHandler($requested_model, $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        if (!$model->isInitialised()) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            $viewModel       = new ViewModel(array(
                'modelName'  =>  $requested_model,
                'errors' => $this->errors
            ));
            return $viewModel->setTemplate('error/admin/model');
        }

        return new ViewModel();
    }

    public function addAction()
    {
        $requested_model = $this->params()->fromRoute('model');
        $model = new ModelHandler($requested_model, $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        if (!$model->isInitialised()) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            $viewModel       = new ViewModel(array(
                'modelName'  =>  $requested_model,
                'errors' => $this->errors
            ));
            return $viewModel->setTemplate('error/admin/model');
        }

        $formManager = new FormHandler($model);

        return new ViewModel(array(
            'form'               => $formManager->getForm(),
            'tabManager'         => $formManager->getTabManager(),
            'multilingualFields' => $model->getModelManager()->getAllMultilingualFields()
        ));
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
}
