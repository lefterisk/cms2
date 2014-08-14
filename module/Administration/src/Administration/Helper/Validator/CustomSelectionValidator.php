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
        'ERROR_2' => 'Incorrect field of type "custom_selections" definition in model config, each definition array requires a key that contains the properly formed name of the field!',
        'ERROR_3' => 'Incorrect field of type "custom_selections" definition in model config, should be an array!',
        'ERROR_4' => 'Incorrect field "%s" of type "custom_selections" definition in model config, parameter "options" is required and needs to be an array!',
        'ERROR_5' => 'Incorrect field "%s" of type "custom_selections" definition in model config, parameter "multiple" is required and needs to be a boolean!',
        'ERROR_6' => 'Incorrect field "%s" of type "custom_selections" definition in model config, if parameter "multiple" is true you need to provide a "lookup_table_name" definition!',
        'ERROR_7' => 'Incorrect field "%s" of type "custom_selections" definition in model config, parameter "lookup_table_name" must be a properly formed table name! (/^[A-Za-z0-9_\-]+$/)',
    );

    public function validate()
    {
        if (is_array($this->definition)) {
            foreach ($this->definition as $field => $definitionArray) {
                if (!$this->isValidVariableName($field)) {
                    $this->errors[] = $this->errorMsgArray['ERROR_2'];
                }
                if (!is_array($definitionArray)) {
                    $this->errors[] = $this->errorMsgArray['ERROR_3'];
                } else {
                    if (!array_key_exists( 'options', $definitionArray) || !is_array($definitionArray['options'])) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_4'], $field);
                    }
                    if (!array_key_exists( 'multiple', $definitionArray) || !is_bool($definitionArray['multiple'])) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_5'], $field);
                    } elseif ($definitionArray['multiple'] && !array_key_exists( 'lookup_table_name', $definitionArray)) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_6'], $field);
                    } elseif($definitionArray['multiple'] && array_key_exists( 'lookup_table_name', $definitionArray) && !$this->isValidVariableName($definitionArray['lookup_table_name'])) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_7'], $field);
                    }
                }
            }
        } else {
            $this->errors[] = $this->errorMsgArray['ERROR_1'];
        }

        return parent::validate();
    }
}