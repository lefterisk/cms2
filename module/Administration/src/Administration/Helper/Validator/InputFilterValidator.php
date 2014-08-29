<?php
namespace Administration\Helper\Validator;

class InputFilterValidator extends AbstractValidator
{
    protected $errorMsgArray = array(
        'ERROR_1' => 'Incorrect field type "Input Filters" definition in model config, should be an array!',
        'ERROR_2' => 'Incorrect definition of individual Input filter: definition should be an array, see manual for proper definition of Input filters!',
        'ERROR_3' => 'Each individual Input filter definition array should contain parameter "name" that corresponds to the name of the field the filter is to be bound on!',
        'ERROR_4a' => 'Input filter definition array for field "%s" should contain either the "required" parameter or the "validator" parameter or both!',
        'ERROR_4b' => 'Each individual Input filter definition array should contain either the "required" parameter or the "validator" parameter or both!',
        'ERROR_5a' => 'Parameter "required" in Input filter definition for field "%s" must be boolean!',
        'ERROR_5b' => 'Parameter "required" in Input filter definitions must be boolean!',
        'ERROR_6a' => 'Parameter "validators" in Input filter definition for field "%s" must be an array!',
        'ERROR_6b' => 'Parameter "validators" in Input filter definition must be an array!',
        'ERROR_7a' => 'Each individual "validator" definition contained in parameter "validators" in Input filter definition for field "%s" must be an array!',
        'ERROR_7b' => 'Each individual "validator" definition contained in parameter "validators" in Input filter definition must be an array!',
        'ERROR_8a' => 'Each individual "validator" definition contained in parameter "validators" in Input filter definition for field "%s" must contain parameter "name" which has to correspond with an available validator Class!',
        'ERROR_8b' => 'Each individual "validator" definition contained in parameter "validators" in Input filter definition must contain parameter "name" which has to correspond with an available validator Class!',
        'ERROR_9a' => 'Parameter "options" in "validator" definition contained in parameter "validators" in Input filter definition for field "%s" must be an array!',
        'ERROR_9b' => 'Parameter "options" in "validator" definition contained in parameter "validators" in Input filter definition must be an array!',
    );

    public function validate()
    {
        if (!is_array($this->definition)) {
            $this->errors[] = $this->errorMsgArray['ERROR_1'];
        } else {
            foreach ($this->definition as $definitionArray) {
                if (!is_array($definitionArray)) {
                    $this->errors[] = $this->errorMsgArray['ERROR_2'];
                }

                if (!array_key_exists('name', $definitionArray)) {
                    $this->errors[] = $this->errorMsgArray['ERROR_3'];
                }

                if (!array_key_exists('required', $definitionArray) && !array_key_exists('validators', $definitionArray)) {
                    if (array_key_exists('name', $definitionArray)) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_4a'], $definitionArray['name']);
                    } else {
                        $this->errors[] = $this->errorMsgArray['ERROR_4b'];
                    }
                }

                if(array_key_exists('required', $definitionArray) && !is_bool($definitionArray['required'])) {
                    if (array_key_exists('name', $definitionArray)) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_5a'], $definitionArray['name']);
                    } else {
                        $this->errors[] = $this->errorMsgArray['ERROR_5b'];
                    }
                }

                if(array_key_exists('validators', $definitionArray) && !is_array($definitionArray['validators'])) {
                    if (array_key_exists('name', $definitionArray)) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_6a'], $definitionArray['name']);
                    } else {
                        $this->errors[] = $this->errorMsgArray['ERROR_6b'];
                    }
                }

                if (array_key_exists('validators', $definitionArray) && is_array($definitionArray['validators'])) {
                    foreach ($definitionArray['validators'] as $validator) {
                       if (!is_array($validator)) {
                           if (array_key_exists('name', $definitionArray)) {
                               $this->errors[] = sprintf($this->errorMsgArray['ERROR_7a'], $definitionArray['name']);
                           } else {
                               $this->errors[] = $this->errorMsgArray['ERROR_7b'];
                           }
                       } else {
                           if (!array_key_exists('name', $validator)) {
                               if (array_key_exists('name', $definitionArray)) {
                                   $this->errors[] = sprintf($this->errorMsgArray['ERROR_8a'], $definitionArray['name']);
                               } else {
                                   $this->errors[] = $this->errorMsgArray['ERROR_8b'];
                               }
                           }

                           if (array_key_exists('options', $validator) && !is_array($validator['options'])) {
                               if (array_key_exists('name', $definitionArray)) {
                                   $this->errors[] = sprintf($this->errorMsgArray['ERROR_9a'], $definitionArray['name']);
                               } else {
                                   $this->errors[] = $this->errorMsgArray['ERROR_9b'];
                               }
                           }
                       }
                    }
                }
            }
        }
        return parent::validate();
    }
}