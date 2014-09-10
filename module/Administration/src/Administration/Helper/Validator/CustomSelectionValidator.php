<?php
namespace Administration\Helper\Validator;

/**
 * Class CustomSelectionValidator
 * @package Administration\Helper\Validator
 * Validate Custom selection fields definitions
 *
 * Proper definition:
 * array(
 *     "field_name_1" => array(
 *         "options"           => array("key" => "value"),
 *         "multiple"          => false, <-- Required
 *         "lookup_table_name" => ""     <-- If "multiple" is true this is required
 *     ),
 *     "field_name_2" => array(
 *         "options"           => array("key" => "value"),
 *         "multiple"          => false, <-- Required
 *         "lookup_table_name" => ""     <-- If "multiple" is true this is required
 *     ),
 *  )
 */
class CustomSelectionValidator extends AbstractValidator
{
    protected $errorMsgArray = array(
        'ERROR_1' => 'Incorrect field type "custom_selections" definition in model config, should be an array!',
        'ERROR_2' => 'Incorrect field of type "custom_selections" definition in model config, parameter "name" is required and needs to be an properly formed field! (/^[A-Za-z0-9_\-]+$/)',
        'ERROR_3' => 'Incorrect field of type "custom_selections" definition in model config, should be an array!',
        'ERROR_4' => 'Incorrect field "%s" of type "custom_selections" definition in model config, parameter "options" is required and needs to be an array!',
        'ERROR_5' => 'Incorrect field "%s" of type "custom_selections" definition in model config, parameter "multiple" is required and needs to be a boolean!',
        'ERROR_6' => 'Incorrect field "%s" of type "custom_selections" definition in model config, if parameter "multiple" is true you need to provide a "lookup_table_name" definition!',
        'ERROR_7' => 'Incorrect field "%s" of type "custom_selections" definition in model config, parameter "lookup_table_name" must be a properly formed table name! (/^[A-Za-z0-9_\-]+$/)',
        'ERROR_8' => 'Incorrect field "%s" of type "custom_selections" definition in model config, every "option" in the "options" array must be a key=>value pair where key must be an integer',
    );

    public function validate()
    {
        if (is_array($this->definition)) {
            foreach ($this->definition as $definitionArray) {
                if (!is_array($definitionArray)) {
                    $this->errors[] = $this->errorMsgArray['ERROR_3'];
                } else {
                    if (!array_key_exists( 'name', $definitionArray)) {
                        $this->errors[] = $this->errorMsgArray['ERROR_2'];
                    } else {
                        if (!$this->isValidVariableName($definitionArray['name'])) {
                            $this->errors[] = $this->errorMsgArray['ERROR_2'];
                        }
                        if (!array_key_exists( 'options', $definitionArray) || !is_array($definitionArray['options'])) {
                            $this->errors[] = sprintf($this->errorMsgArray['ERROR_4'], $definitionArray['name']);
                        } else {
                            foreach ($definitionArray['options'] as $key => $option) {
                                if (!is_int($key)) {
                                    $this->errors[] = sprintf($this->errorMsgArray['ERROR_8'], $definitionArray['name']);
                                }
                            }
                        }
                        if (!array_key_exists( 'multiple', $definitionArray) || !is_bool($definitionArray['multiple'])) {
                            $this->errors[] = sprintf($this->errorMsgArray['ERROR_5'], $definitionArray['name']);
                        } elseif ($definitionArray['multiple'] && !array_key_exists( 'lookup_table_name', $definitionArray)) {
                            $this->errors[] = sprintf($this->errorMsgArray['ERROR_6'], $definitionArray['name']);
                        } elseif($definitionArray['multiple'] && array_key_exists( 'lookup_table_name', $definitionArray) && !$this->isValidVariableName($definitionArray['lookup_table_name'])) {
                            $this->errors[] = sprintf($this->errorMsgArray['ERROR_7'], $definitionArray['name']);
                        }
                    }
                }
            }
        } else {
            $this->errors[] = $this->errorMsgArray['ERROR_1'];
        }

        return parent::validate();
    }
}