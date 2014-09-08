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
use Zend\Form\Form;
use Zend\Json\Server\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class ModelController extends AbstractActionController
{
    protected $errors = array();

    public function indexAction()
    {
        $requested_model = $this->params()->fromRoute('model');
        $model           = new ModelHandler($requested_model, $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));

        if (!$model->isInitialised()) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            $viewModel       = new ViewModel(array(
                'modelName' =>  $requested_model,
                'errors'    => $this->errors
            ));
            return $viewModel->setTemplate('error/admin/model');
        }

        $listingHandler     = new ListingHandler($model, $this->getServiceLocator()->get('SiteLanguages'));
        $multipleDeleteForm = new Form();
        $multipleDeleteForm->setAttribute('action',  $this->url()->fromRoute('administration/model', array('action' => 'delete-multiple', 'model' => $requested_model)));
        $multipleDeleteForm->setAttribute('method', 'post');

        return new ViewModel(array(
            'multipleDeleteForm' => $multipleDeleteForm,
            'model'              => $requested_model,
            'listing'            => $listingHandler->getListing(),
            'listingFields'      => $listingHandler->getListingFieldsDefinitions()
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
            $form->setInputFilter($model->getOverAllInputFilter());

            $form->setData($formManager->preparePostData($request->getPost()));
            if ($form->isValid()) {
                $model->save($form->getData());
                return $this->redirectToModelAction($requested_model, 'index');
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
        $requested_model = $this->params()->fromRoute('model');
        $requested_item  = $this->params()->fromRoute('item');
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

        try {
            $item = $model->getItemById($requested_item);
            $form->setInputFilter($model->getOverAllInputFilter());
            $form->setData($formManager->preparePostData($item));
        } catch (\Exception $ex) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            $viewModel       = new ViewModel(array(
                'modelName'  =>  $requested_model,
                'errors'     => $this->errors
            ));
            return $viewModel->setTemplate('error/admin/model');
        }

        if ($request->isPost()) {
            $form->setInputFilter($model->getOverAllInputFilter());

            $form->setData($formManager->preparePostData($request->getPost()));
            if ($form->isValid()) {
                $model->save($form->getData());
            }
        }

        return new ViewModel(array(
            'model'              => $requested_model,
            'form'               => $form,
            'tabManager'         => $formManager->getTabManager(),
            'siteLanguages'      => $this->getServiceLocator()->get('SiteLanguages')->getLanguages(),
            'multilingualFields' => $model->getModelManager()->getAllMultilingualFields()
        ));
    }

    public function deleteAction()
    {
        $requested_model = $this->params()->fromRoute('model');
        $requested_item  = $this->params()->fromRoute('item');

        $model = new ModelHandler($requested_model, $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        if (!$model->isInitialised()) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            $viewModel       = new ViewModel(array(
                'modelName'  =>  $requested_model,
                'errors' => $this->errors
            ));
            return $viewModel->setTemplate('error/admin/model');
        }


        try {
            $model->deleteItemById($requested_item);
            return $this->redirectToModelAction($requested_model, 'index');
        }
        catch (\Exception $ex) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            $viewModel       = new ViewModel(array(
                'modelName'  =>  $requested_model,
                'errors'     => $this->errors
            ));
            return $viewModel->setTemplate('error/admin/model');
        }
    }

    public function deleteMultipleAction()
    {
        $requested_model = $this->params()->fromRoute('model');
        $itemsToDelete   = $this->getRequest()->getPost('multipleDeleteCheck');

        $model = new ModelHandler($requested_model, $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        if (!$model->isInitialised()) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            $viewModel       = new ViewModel(array(
                'modelName'  =>  $requested_model,
                'errors' => $this->errors
            ));
            return $viewModel->setTemplate('error/admin/model');
        }

        try {
            $model->deleteMultipleItemsById($itemsToDelete);
            return $this->redirectToModelAction($requested_model, 'index');
        }
        catch (\Exception $ex) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            $viewModel       = new ViewModel(array(
                'modelName'  =>  $requested_model,
                'errors'     => $this->errors
            ));
            return $viewModel->setTemplate('error/admin/model');
        }
    }

    protected function redirectToModelAction($model, $action)
    {
        return $this->redirect()->toRoute('administration/model', array(
            'action' => $action,
            'model'  => $model
        ));
    }
}
