<?php
namespace Administration\Helper\Validator;

/**
 * Class FormManagerValidator
 * @package Administration\Helper\Validator
 * Validates Form Manager definition array
 *
 * Proper definition:
 * array(
 *    "tab_name_1" => array(
 *       "field_name_1",
 *       "field_name_2",
 *       ......
 *    ),
 *    "tab_name_2" => array(
 *       "field_name_3",
 *       "field_name_4",
 *       ......
 *    ),
 *    ....
 * ),
 */
class FormManagerValidator extends AbstractValidator
{
    protected $errorMsgArray = array(
        'ERROR_1' => 'Incorrect field type "form_manager" definition in model config, should be an array!',
        'ERROR_2' => 'Incorrect field of type "form_manager" definition in model config, each definition array requires a key that contains the properly formed name of the tab!',
        'ERROR_3' => 'Incorrect field of type "form_manager" definition in model config, should be an array!',
        'ERROR_4' => 'Incorrect field "%s" in tab "%s" definition in model config, tab contents (fields) must be properly formed strings!',
    );

    public function validate()
    {
        if (!is_array($this->definition)) {
            $this->errors[] = $this->errorMsgArray['ERROR_1'];
        } else {
            foreach ($this->definition as $tabName => $fieldsArray) {
                if (!$this->isValidVariableName($tabName)) {
                    $this->errors[] = $this->errorMsgArray['ERROR_2'];
                }
                if (!is_array($fieldsArray)) {
                    $this->errors[] = $this->errorMsgArray['ERROR_3'];
                } else {
                    foreach ($fieldsArray as $field) {
                        if (!$this->isValidVariableName($field)) {
                            $this->errors[] = sprintf($this->errorMsgArray['ERROR_4'], $field, $tabName);
                        }
                    }
                }
            }
        }
        return parent::validate();
    }
}