<?php
namespace Administration\Helper;

use Administration\Helper\DbGateway\SiteLanguageHelper;
use Zend\Form\Form;

class FormHandler
{
    protected $modelHandler;
    protected $form;
    protected $tabManager = array();
    protected $languageHelper;

    public function __construct(ModelHandler $modelHandler, SiteLanguageHelper $languageHelper)
    {
        $this->modelHandler   = $modelHandler;
        $this->languageHelper = $languageHelper;

        if (!is_array($this->modelHandler->getModelManager()->getFormManager()) || count($this->modelHandler->getModelManager()->getFormManager()) <= 0 ) {
            $this->tabManager = $this->getDefaultTabManager();
        } else {
            $this->tabManager = $this->modelHandler->getModelManager()->getFormManager();
        }

        $this->form = $this->initialiseForm();
    }

    private function getDefaultTabManager()
    {
        $tabManager = array();
        if (count($this->modelHandler->getModelManager()->getSimpleFields()) > 0) {
            $tabManager['simple_fields'] = $this->modelHandler->getModelManager()->getSimpleFields();
        }
        if (count($this->modelHandler->getModelManager()->getAdvancedFields()) > 0) {
            $tabManager['editor_fields'] = $this->modelHandler->getModelManager()->getAdvancedFields();
        }
        if (count($this->modelHandler->getModelManager()->getAllFileFields()) > 0) {
            $tabManager['file_fields']   = $this->modelHandler->getModelManager()->getAllFileFields();
        }
        if ($this->modelHandler->getModelManager()->isStandAlonePage()) {
            $tabManager['meta_fields']   = $this->modelHandler->getModelManager()->getMetaFields();
        }
        return $tabManager;
    }

    protected function initialiseForm()
    {
        $form = new Form($this->modelHandler->getModelManager()->getModelName());

        $form->add(array(
            'type' => 'hidden',
            'name' => 'id',
        ));

        foreach ($this->modelHandler->getModelManager()->getAllFields() as $field) {
            $type          = 'Zend\Form\Element\Text';
            $attributes    = array();
            $value_options = array();
            $name          = '';
            $label         = '';

            if (in_array($field, array_merge($this->modelHandler->getModelManager()->getIntegers(), $this->modelHandler->getModelManager()->getVarchars(), $this->modelHandler->getModelManager()->getMultilingualVarchars(),  $this->modelHandler->getModelManager()->getFileCaptions(), $this->modelHandler->getModelManager()->getMultilingualFilesCaptions())) ) {

                $type       = 'Zend\Form\Element\Text';
                $attributes = array('class' => 'form-control');
                $name       = $field;
                $label      = $this->modelHandler->getModelManager()->getPrefix() . $field;

            } elseif (in_array($field, array_merge($this->modelHandler->getModelManager()->getTexts(), $this->modelHandler->getModelManager()->getMultilingualTexts()))) {

                $type       = 'Zend\Form\Element\Textarea';
                $attributes = array('class' => 'form-control');
                $name       = $field;
                $label      = $this->modelHandler->getModelManager()->getPrefix() . $field;

            } elseif (in_array($field, array_merge($this->modelHandler->getModelManager()->getLongTexts(), $this->modelHandler->getModelManager()->getMultilingualLongTexts()))) {

                $type       = 'Zend\Form\Element\Textarea';
                $attributes = array('class' => 'tinyMce');
                $name       = $field;
                $label      = $this->modelHandler->getModelManager()->getPrefix() . $field;

            } elseif (in_array($field, $this->modelHandler->getModelManager()->getBooleans())) {

                $type        = 'Zend\Form\Element\Checkbox';
                $attributes  = array('class' => 'bootstrapSwitchEdit');
                $name       = $field;
                $label      = $this->modelHandler->getModelManager()->getPrefix() . $field;

            } elseif (in_array($field,$this->modelHandler->getModelManager()->getDates())) {

                $type       = 'Zend\Form\Element\Text';
                $attributes = array(
                    'class'     => 'datePicker form-control',
                    'readonly'  => 'readonly',
                    'data-type' => 'date',
                );
                $name       = $field;
                $label      = $this->modelHandler->getModelManager()->getPrefix() . $field;

            } elseif (in_array($field, array_merge($this->modelHandler->getModelManager()->getFiles(), $this->modelHandler->getModelManager()->getMultilingualFiles()))) {

                $type       = 'Zend\Form\Element\Text';
                $attributes = array('class' => 'form-control', 'id' => $field);
                $attributes = array_merge($attributes,array('data-type' => 'file'));
                $name       = $field;
                $label      = $this->modelHandler->getModelManager()->getPrefix() . $field;

            }

            if (in_array($field, $this->modelHandler->getModelManager()->getAllMultilingualFields())) {
                foreach ($this->languageHelper->getLanguages() as $languageId => $language) {
                    if (array_key_exists('id', $attributes)) {
                        $attributes['id'] = $attributes['id'] . '-' . $languageId;
                    }
                    $form->add(array(
                        'type'       => $type,
                        'name'       => $name . '[' . $languageId . ']',
                        'options'    => array(
                            'label'         => $label,
                            'value_options' => $value_options,
                        ),
                        'attributes' => array_merge($attributes,array('placeholder' => $name)),
                    ));
                }
            } else {
                $form->add(array(
                    'type'       => $type,
                    'name'       => $name,
                    'options'    => array(
                        'label'         => $label,
                        'value_options' => $value_options,
                        'use_hidden_element' => true,
                        'checked_value'      => '1',
                        'unchecked_value'    => '0'
                    ),
                    'attributes' => array_merge($attributes,array('placeholder' => $name)),
                ));
            }
        }
        return $form;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function getTabManager()
    {
        return $this->tabManager;
    }

    public function preparePostData($post)
    {
        $returnArray = array();
        foreach ($post as $field => $values) {
            if (in_array($field,  $this->modelHandler->getModelManager()->getAllMultilingualFields()) && is_array($values)) {
                foreach ($values as $languageId => $fieldValue) {
                    $returnArray[$field . '[' . $languageId . ']'] = $fieldValue;
                }
            } else {
                $returnArray[$field] = $values;
            }
        }
        return $returnArray;
    }
}