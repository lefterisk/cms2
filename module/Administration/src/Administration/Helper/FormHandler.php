<?php
namespace Administration\Helper;

use Administration\Helper\DbGateway\SiteLanguageHelper;
use Zend\Db\Sql\Expression;
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
        $tabManager     = array();
        $firstTabFields = array();
        if ($this->modelHandler->getModelManager()->getMaximumTreeDepth() > 0) {
            $firstTabFields = array($this->modelHandler->getParentManager()->getFieldName());
        }
        $firstTabFields = array_merge(
            $firstTabFields,
            $this->modelHandler->getRelationFieldsNames(),
            $this->modelHandler->getCustomSelectionFieldsNames(),
            $this->modelHandler->getModelManager()->getSimpleFields()
        );
        if (count($firstTabFields) > 0) {
            $tabManager['simple_fields'] = $firstTabFields;
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

        $form->add(array(
            'type' => 'hidden',
            'name' => 'redirect_after_save',
        ));

        if ($this->modelHandler->getModelManager()->getMaximumTreeDepth() > 0) {
            $form = $this->addParentFieldToForm($form);
        }

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
        $form = $this->addRelationFieldsToForm($form);
        $form = $this->addCustomSelectionFieldsToForm($form);
        return $form;
    }

    protected function addParentFieldToForm(Form $form)
    {
        $value_options             = array('0' => '---Root Item---');
        $joinDefinitions           = array();
        $additionalWhereStatements = array();
        $orderStatements           = array();

        if ($this->modelHandler->getTranslationManager()->requiresTable()) {
            //translations
            $joinDefinitions[] = $this->modelHandler->getTranslationManager()->getTranslationTableJoinDefinition($this->languageHelper->getPrimaryLanguageId());
        }

        if ($this->modelHandler->getParentManager()->requiresTable()) {
            //parent relation

            $joinDefinitions = array_merge($joinDefinitions, $this->modelHandler->getParentManager()->getParentTableJoinDefinition(0));
            $orderStatements[] = 'breadcrumbs';
        }

        $results = $this->modelHandler->getModelTable()->fetch(
            $this->modelHandler->getModelManager()->getTableSpecificListingFields(
                $this->modelHandler->getModelManager()->getListingFields()
            ),
            $joinDefinitions,
            $additionalWhereStatements,
            $orderStatements
        );

        foreach ($results as $listingItem) {
            $optionString = '';
            for ($i = 1; $i <= $listingItem->depth -1 ; $i++) {
                if ($i == 1) {
                    $optionString .= '|';
                }
                $optionString .= '--';
            }
            foreach ($this->modelHandler->getModelManager()->getListingFields() as $listingField) {
                $optionString .= $listingItem->{$listingField} . ' ';
            }
            $value_options[$listingItem->id] = $optionString;
        }

        $form->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'parent_id',
            'options' => array(
                'label' => 'parent_id',
                'value_options' => $value_options,
            ),
            'attributes' => array('class' => 'form-control'),
        ));
        return $form;
    }

    protected function addRelationFieldsToForm(Form $form)
    {
        foreach($this->modelHandler->getRelationHandlers() as $relationHandler) {
            if ($relationHandler instanceof RelationHandler) {

                $valueOptions = array();
                $multiple     = '';

                if ($relationHandler->getRelationManager()->requiresColumn()) {
                    //if requiresColumn = true we show a single choice select drop-down
                    $valueOptions = array('0' => 'Please Choose');
                } elseif ($relationHandler->getRelationManager()->requiresTable()) {
                    //Else if requiresTable = true show a multi-choice select box
                    $multiple = 'multiple';
                }

                foreach ($relationHandler->getRelatedModelTable()->fetchForRelationSelect($relationHandler->getRelationManager()->getFieldsToReturn()) as $relationData) {
                    $valueOptions[$relationData->id] = '';
                    foreach ($relationHandler->getRelationManager()->getFieldsToReturn() as $returnedField) {
                        $valueOptions[$relationData->id] .= $relationData->{$returnedField} . ' ';
                    }
                }

                $form->add(array(
                    'type'       => 'Zend\Form\Element\Select',
                    'name'       => $relationHandler->getRelationManager()->getFieldName(),
                    'options'    => array(
                        'label'         => $relationHandler->getRelationManager()->getFieldName(),
                        'value_options' => $valueOptions,
                    ),
                    'attributes' => array(
                        'class'    => 'form-control',
                        'multiple' => $multiple
                    )
                ));
            }
        }
        return $form;
    }

    protected function addCustomSelectionFieldsToForm(Form $form)
    {
        foreach($this->modelHandler->getCustomSelectionHandlers() as $customSelectionHandler) {
            if ($customSelectionHandler instanceof CustomSelectionHandler) {

                $multiple     = '';

                if ($customSelectionHandler->getCustomSelectionManager()->requiresTable()) {
                    //If requiresTable = true show a multi-choice select box
                    $multiple = 'multiple';
                }

                $form->add(array(
                    'type'       => 'Zend\Form\Element\Select',
                    'name'       => $customSelectionHandler->getCustomSelectionManager()->getFieldName(),
                    'options'    => array(
                        'label'         => $customSelectionHandler->getCustomSelectionManager()->getFieldName(),
                        'value_options' => $customSelectionHandler->getCustomSelectionManager()->getOptionsForSelect(),
                    ),
                    'attributes' => array(
                        'class'    => 'form-control',
                        'multiple' => $multiple
                    )
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
            } elseif (in_array($field,  $this->modelHandler->getModelManager()->getIntegers()) && empty($values)) {
                $returnArray[$field] = 0;
            } else {
                $returnArray[$field] = $values;
            }
        }
        return $returnArray;
    }
}