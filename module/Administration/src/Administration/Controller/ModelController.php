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
use Administration\Helper\ListingHandler;
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

        $listingHandler = new ListingHandler($model, $this->getServiceLocator()->get('SiteLanguages'));

        return new ViewModel(array(
            'listing' => $listingHandler->getListing()
        ));
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

        $formManager = new FormHandler($model, $this->getServiceLocator()->get('SiteLanguages'));

        $request = $this->getRequest();
        $form    = $formManager->getForm();

        if ($request->isPost()) {
            $form->setInputFilter($model->getModelManager()->getInputFilter());

            $form->setData($formManager->preparePostData($request->getPost()));
            if ($form->isValid()) {
                $model->save($form->getData());
            }
        }

        return new ViewModel(array(
            'form'               => $form,
            'tabManager'         => $formManager->getTabManager(),
            'siteLanguages'      => $this->getServiceLocator()->get('SiteLanguages')->getLanguages(),
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
