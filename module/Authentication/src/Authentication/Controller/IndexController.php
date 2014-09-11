<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Authentication\Controller;

use Zend\InputFilter\InputFilter;
use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $request      = $this->getRequest();
        $form         = $this->getForm();
        $formMessages = array();
        $authService  = $this->getServiceLocator()->get('AuthService');

        if ($request->isPost()) {
            $form->setInputFilter($this->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $authService->getAdapter()->setIdentity($this->params()->fromPost('email'));
                $authService->getAdapter()->setCredential($this->params()->fromPost('password'));
                $result = $authService->authenticate();
                foreach($result->getMessages() as $message)
                {
                    $formMessages[] = $message;
                }
            }
        }

        if ($authService->hasIdentity()) {
            return $this->redirect()->toRoute('administration');
        }

        return new ViewModel(array(
            'form'     => $form,
            'messages' => $formMessages
        ));
    }

    protected function getInputFilter()
    {
        $inputFilter = new InputFilter();
        $inputFilter->add(array(
            'name' => 'email',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'not_empty',
                ),
                array(
                    'name' => 'Zend\Validator\EmailAddress',
                ),
            )
        ));
        $inputFilter->add(array(
            'name' => 'password',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'not_empty',
                ),
            )
        ));
        return $inputFilter;
    }

    protected function getForm()
    {
        $form = new Form('login');
        $form->add(array(
            'type'       => 'Zend\Form\Element\Email',
            'name'       => 'email',
            'options'    => array(
                'label' => 'Email:',
            ),
            'attributes' => array(
                'placeholder' => 'Email',
                'class' => 'form-control'
            )
        ));

        $form->add(array(
            'type'       => 'Zend\Form\Element\Password',
            'name'       => 'password',
            'options'    => array(
                'label' => 'Password:',
            ),
            'attributes' => array(
                'placeholder' => 'Password',
                'class' => 'form-control'
            )
        ));

        $form->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'remember',
            'options' => array(
                'label'              => 'Remember me',
                'use_hidden_element' => true,
                'checked_value'      => '1',
                'unchecked_value'    => '0'
            )
        ));

        return $form;
    }
}
