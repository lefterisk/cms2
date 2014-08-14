<?php
namespace Administration\Helper\Validator;

class ModelValidator extends AbstractValidator
{
    protected $requiredModelSettings = array(
        'model_name',
        'table_name',
        'prefix',
    );
    protected $modelSettings         = array(
        'model_name'      => 'string',
        'table_name'      => 'string',
        'prefix'          => 'string',
        'is_stand_alone'  => 'boolean',
        'fields'          => 'array',
        'max_tree_depth'  => 'int',
        'listing_fields'  => 'array',
        'required_fields' => 'array',
        'form_manager'    => 'array',
        'input_filters'   => 'array',
    );
    protected $modelSimpleFieldTypes  = array(
        'dates',
        'booleans',
        'varchars',
        'texts',
        'long_texts',
        'integers',
        'files',
        'multilingual_varchars',
        'multilingual_texts',
        'multilingual_long_texts',
        'multilingual_files',
    );

    protected $errorMsgArray = array(
        'ERROR_1' => 'Missing required "%s" definition in model config!',
        'ERROR_2' => 'Incorrect "%s" type in model config, should be a string!',
        'ERROR_3' => 'Incorrect "%s" type in model config, should be a array!',
        'ERROR_4' => 'Incorrect "%s" type in model config, should be a boolean!',
        'ERROR_5' => 'Incorrect "%s" type in model config, should be a integer!',
        'ERROR_6' => 'Incorrect field type "%s" definition in model config, should be an array!',
        'ERROR_7' => 'Incorrect "%s" field "%s" definition in model config, should be a string and not contain spaces or funny chars! (/^[A-Za-z0-9_\-]+$/)',
    );

        /**
     * Validates model config array populates errors array
     * @return bool
     */
    public function validate()
    {
        //Check for mandatory definitions
        $this->checkModelSettings();

        //Check for field definitions integrity
        $this->checkFieldDefinitionIntegrity();

        //Check form manager
        $this->checkFormManager();

        return parent::validate();
    }

    private function checkModelSettings()
    {
        foreach ($this->modelSettings as $setting => $type) {
            if (in_array($setting, $this->requiredModelSettings) && (!array_key_exists($setting, $this->definition) || empty($this->definition[$setting]))) {
                $this->errors[] = sprintf($this->errorMsgArray['ERROR_1'], $setting);
            } elseif (array_key_exists($setting, $this->definition)) {
                switch ($type) {
                    case 'string':
                        if (!is_string($this->definition[$setting])) {
                            $this->errors[] = sprintf($this->errorMsgArray['ERROR_2'], $setting);
                        }
                        break;
                    case 'array':
                        if (!is_array($this->definition[$setting])) {
                            $this->errors[] = sprintf($this->errorMsgArray['ERROR_3'], $setting);
                        }
                        break;
                    case 'boolean':
                        if (!is_bool($this->definition[$setting])) {
                            $this->errors[] = sprintf($this->errorMsgArray['ERROR_4'], $setting);
                        }
                        break;
                    case 'int':
                        if (!is_integer($this->definition[$setting])) {
                            $this->errors[] = sprintf($this->errorMsgArray['ERROR_5'], $setting);
                        }
                        break;
                }
            }
        }
    }

    private function checkFieldDefinitionIntegrity()
    {
        if (array_key_exists('fields',$this->definition) && is_array($this->definition['fields'])) {
            //simple fields
            $this->checkSimpleFields();
            //custom selections
            $this->checkCustomSelectionFields();
            //relations
            $this->checkRelationFields();
        }
    }

    private function checkSimpleFields()
    {
        foreach ($this->modelSimpleFieldTypes as $fieldType) {
            if (array_key_exists($fieldType, $this->definition['fields']) && !is_array($this->definition['fields'][$fieldType])) {
                $this->errors[] = sprintf($this->errorMsgArray['ERROR_6'], $fieldType);
            } else {
                foreach ($this->definition['fields'][$fieldType] as $field) {
                    if (!$this->isValidVariableName($field)) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_7'], $fieldType, $field);
                    }
                }
            }
        }
    }

    private function checkCustomSelectionFields()
    {
        if (array_key_exists('custom_selections', $this->definition['fields']))
        {
            $validator = new CustomSelectionValidator($this->definition['fields']['custom_selections']);
            if (!$validator->validate()) {
                $this->errors = array_merge($this->errors, $validator->getErrors());
            }
        }
    }

    private function checkRelationFields()
    {
        if (array_key_exists('relations', $this->definition['fields']))
        {
            $validator = new RelationValidator($this->definition['fields']['relations']);
            if (!$validator->validate()) {
                $this->errors = array_merge($this->errors, $validator->getErrors());
            }
        }
    }

    private function checkFormManager()
    {
        if (array_key_exists('form_manager', $this->definition))
        {
            $validator = new FormManagerValidator($this->definition['form_manager']);
            if (!$validator->validate()) {
                $this->errors = array_merge($this->errors, $validator->getErrors());
            }
        }
    }
}