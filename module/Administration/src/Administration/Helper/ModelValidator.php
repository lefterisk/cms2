<?php
namespace Administration\Helper;

class ModelValidator
{
    protected $errors                = array();
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
    protected $model;

    public function __construct(Array $model)
    {
        $this->model = $model;
    }

    public function getErrors()
    {
        return $this->errors;
    }

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

        if (count($this->errors) > 0) {
            return false;
        }
        return true;
    }

    private function checkModelSettings()
    {
        foreach ($this->modelSettings as $setting => $type) {
            if (in_array($setting, $this->requiredModelSettings) && !array_key_exists($setting, $this->model)) {
                $this->errors[] = 'Missing required "'. $setting . '" definition in model config!';
            } elseif (array_key_exists($setting, $this->model)) {
                switch ($type) {
                    case 'string':
                        if (!is_string($this->model[$setting])) {
                            $this->errors[] = 'Incorrect "'. $setting . '" type in model config, should be a string!';
                        }
                        break;
                    case 'array':
                        if (!is_array($this->model[$setting])) {
                            $this->errors[] = 'Incorrect "'. $setting . '" type in model config, should be an array!';
                        }
                        break;
                    case 'boolean':
                        if (!is_bool($this->model[$setting])) {
                            $this->errors[] = 'Incorrect "'. $setting . '" type in model config, should be a boolean!';
                        }
                        break;
                    case 'int':
                        if (!is_integer($this->model[$setting])) {
                            $this->errors[] = 'Incorrect "'. $setting . '" type in model config, should be an integer!';
                        }
                        break;
                }
                if (in_array($setting, $this->requiredModelSettings) && empty($this->model[$setting])) {
                    $this->errors[] = '"'. $setting . '" is required, please provide a value!';
                }
            }
        }
    }

    private function checkFieldDefinitionIntegrity()
    {
        if (array_key_exists('fields',$this->model) && is_array($this->model['fields'])) {
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
            if (array_key_exists($fieldType, $this->model['fields']) && !is_array($this->model['fields'][$fieldType])) {
                $this->errors[] = 'Incorrect field type "'. $fieldType . '" definition in model config, should be an array!';
            } else {
                foreach ($this->model['fields'][$fieldType] as $field) {
                    if (!$this->isValidVariableName($field)) {
                        $this->errors[] = 'Incorrect "' . $fieldType . '" field "'. $field . '" definition in model config, should be a string and not contain spaces or funny chars! (/^[A-Za-z0-9_\-]+$/)';
                    }
                }
            }
        }
    }

    private function checkCustomSelectionFields()
    {
        if (array_key_exists('custom_selections', $this->model['fields']) && is_array($this->model['fields']['custom_selections'])) {
            foreach ($this->model['fields']['custom_selections'] as $field => $definition) {
                if (!$this->isValidVariableName($field)) {
                    $this->errors[] = 'Incorrect field of type "custom_selections" definition in model config, each definition array requires a key that contains the properly formed name of the field!';
                }
                if (!is_array($definition)) {
                    $this->errors[] = 'Incorrect field of type "custom_selections" definition in model config, should be an array!';
                } else {
                    if (!array_key_exists( 'options', $definition) || !is_array($definition['options'])) {
                        $this->errors[] = 'Incorrect field "' . $field . '" of type "custom_selections" definition in model config, parameter "options" is required and needs to be an array!';
                    }
                    if (!array_key_exists( 'multiple', $definition) || !is_bool($definition['multiple'])) {
                        $this->errors[] = 'Incorrect field "' . $field . '" of type "custom_selections" definition in model config, parameter "multiple" is required and needs to be a boolean!';
                    } elseif ($definition['multiple'] && !array_key_exists( 'lookup_table_name', $definition)) {
                        $this->errors[] = 'Incorrect field "' . $field . '" of type "custom_selections" definition in model config, if parameter "multiple" is true you need to provide a "lookup_table_name" definition!';
                    } elseif($definition['multiple'] && array_key_exists( 'lookup_table_name', $definition) && !$this->isValidVariableName($definition['lookup_table_name'])) {
                        $this->errors[] = 'Incorrect field "' . $field . '" of type "custom_selections" definition in model config, parameter "lookup_table_name" must be a properly formed table name! (/^[A-Za-z0-9_\-]+$/)';
                    }
                }
            }
        } else {
            $this->errors[] = 'Incorrect field type "custom_selections" definition in model config, should be an array!';
        }
    }

    private function checkRelationFields()
    {
        if (array_key_exists('relations', $this->model['fields']) && is_array($this->model['fields']['relations'])) {
            foreach ($this->model['fields']['relations'] as $field => $definition) {
                if (!$this->isValidVariableName($field)) {
                    $this->errors[] = 'Incorrect field of type "relations" definition in model config, each definition array requires a key that contains the properly formed name of the field!';
                }
                if (!is_array($definition)) {
                    $this->errors[] = 'Incorrect field of type "relations" definition in model config, should be an array!';
                } else {
                    if (!array_key_exists( 'related_model', $definition) || !$this->isValidVariableName($definition['related_model'])) {
                        $this->errors[] = 'Incorrect field "' . $field . '" of type "relations" definition in model config, parameter "related_model" is required and needs to be a string!';
                    }
                    if (!array_key_exists( 'fields_for_select', $definition) || !is_array($definition['fields_for_select'])) {
                        $this->errors[] = 'Incorrect field "' . $field . '" of type "relations" definition in model config, parameter "fields_for_select" is required and needs to be an array!';
                    } else {
                        foreach ($definition['fields_for_select'] as $field) {
                            if ($this->isValidVariableName($field)) {
                                $this->errors[] = 'Incorrect field "' . $field . '" of type "relations" definition in model config, "fields_for_select" contents must be properly formed strings!';
                            }
                        }
                    }
                    if (!array_key_exists( 'relation_type', $definition) || empty($definition['relation_type']) || !in_array($definition['relation_type'], array('oneToMany', 'manyToOne', 'manyToMany'))) {
                        $this->errors[] = 'Incorrect field "' . $field . '" of type "relations" definition in model config, parameter "relation_type" is required and needs to be one of: "oneToMany", "manyToOne", "manyToMany" !';
                    } elseif ($definition['relation_type'] == 'manyToMany' && !array_key_exists( 'lookup_table_name', $definition)) {
                        $this->errors[] = 'Incorrect field "' . $field . '" of type "relations" definition in model config, if parameter "relation_type" is "manyToMany" you need to provide a "lookup_table_name" definition';
                    }
                    if (array_key_exists( 'lookup_table_name', $definition) && !$this->isValidVariableName($definition['lookup_table_name'])) {
                        $this->errors[] = 'Incorrect field "' . $field . '" of type "relations" definition in model config, parameter "lookup_table_name" must be a properly formed table name! (/^[A-Za-z0-9_\-]+$/)';
                    }
                }
            }
        }
    }

    private function isValidVariableName($string)
    {
        if (is_string($string) && !empty($string) && preg_match('/^[A-Za-z0-9_\-]+$/', $string)) {
           return true;
        }
        return false;
    }
}