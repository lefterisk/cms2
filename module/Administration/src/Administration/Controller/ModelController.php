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
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\Form\Form;
use Zend\Json\Server\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class ModelController extends AbstractActionController implements EventManagerAwareInterface
{
    protected $errors = array();

    public function indexAction()
    {
        $requested_model  = $this->params()->fromRoute('model');

        //identity & acl comes from module.php (bootstrap)
        if (!$this->acl->isAllowed($this->identity['user_group_name'], null, 'view')) {
            return $this->notPermittedViewmodel($requested_model);
        }

        $model            = new ModelHandler($requested_model, $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        if (!$model->isInitialised()) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            $viewModel    = new ViewModel(array(
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
            'listingFields'      => $listingHandler->getListingFieldsDefinitions(),
            'userGroup'          => $this->identity['user_group_name'],
            'permissionHelper'   => $this->acl
        ));
    }

    public function addAction()
    {
        $requested_model = $this->params()->fromRoute('model');

        //identity & acl comes from module.php (bootstrap)
        if (!$this->acl->isAllowed($this->identity['user_group_name'], null, 'add')) {
            return $this->notPermittedViewmodel($requested_model);
        }

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
                $this->getEventManager()->trigger('logAction', null, array('type' => 'info', 'message' => 'Added item of type "' . ucfirst($requested_model) . '"'));
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

        //identity & acl comes from module.php (bootstrap)
        if (!$this->acl->isAllowed($this->identity['user_group_name'], null, 'edit')) {
            return $this->notPermittedViewmodel($requested_model);
        }

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
        $form->setInputFilter($model->getOverAllInputFilter());

        if ($request->isPost()) {
            $form->setData($formManager->preparePostData($request->getPost()));
            if ($form->isValid()) {
                $model->save($form->getData());
                $this->getEventManager()->trigger('logAction',null,array('type' => 'info','message' => 'Edited item of type "' . ucfirst($requested_model) . '" with id ="' . $requested_item . '"'));
                $redirect = $this->params()->fromPost('redirect_after_save');
                if (!empty($redirect) && $redirect == 1) {
                    return $this->redirectToModelAction($requested_model, 'index');
                }
            }
        }

        try {
            $item = $model->getItemById($requested_item);
            $form->setData($formManager->preparePostData($item));
        } catch (\Exception $ex) {
            $this->errors = array_merge($this->errors, $model->getErrors());
            $viewModel    = new ViewModel(array(
                'modelName' =>  $requested_model,
                'errors'    => $this->errors
            ));
            return $viewModel->setTemplate('error/admin/model');
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

        //identity & acl comes from module.php (bootstrap)
        if (!$this->acl->isAllowed($this->identity['user_group_name'], null, 'delete')) {
            return $this->notPermittedViewmodel($requested_model);
        }

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
            $this->getEventManager()->trigger('logAction',null,array('type' => 'warn','message' => 'Deleted item of type "' . ucfirst($requested_model) . '" with id ="' . $requested_item . '"'));
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

        //identity & acl comes from module.php (bootstrap)
        if (!$this->acl->isAllowed($this->identity['user_group_name'], null, 'delete')) {
            return $this->notPermittedViewmodel($requested_model);
        }

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
            $this->getEventManager()->trigger('logAction',null,array('type' => 'warn','message' => 'Deleted items of type "' . ucfirst($requested_model) . '" with ids ="' . implode(",",$itemsToDelete) . '"'));
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

    public function notPermittedViewmodel($requested_model)
    {
        $viewModel = new ViewModel(array(
            'modelName' => $requested_model,
            'errors'    => array('You are are not authorised for this action')
        ));
        return $viewModel->setTemplate('error/admin/model');
    }

    protected function redirectToModelAction($model, $action)
    {
        return $this->redirect()->toRoute('administration/model', array(
            'action' => $action,
            'model'  => $model
        ));
    }

    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }
}
