<?php
namespace Administration\Helper\Validator;

/**
 * Class ActionManagerValidator
 * @package Administration\Helper\Validator
 * Validates Action Manager definition array
 *
 * Proper definition:
 * array(
 *   'add'         => function($data) { return $data; },
 *   'edit'        => function($data) { return $data; },
 *   'delete'      => function($data) { return $data; },
 *   'preShowForm' => function($data) { return $data; }
 * )
 */
class ActionManagerValidator extends AbstractValidator
{
    protected $errorMsgArray = array(
        'ERROR_1' => 'Incorrect field type "action_manager" definition in model config, should be an array!',
        'ERROR_2' => 'Incorrect field of type "action_manager" definition in model config, "%s" is not an available action!',
        'ERROR_3' => 'Incorrect field of type "action_manager" definition in model config, action "%s" should be a callable function',
        'ERROR_4' => 'Incorrect field of type "action_manager" definition in model config, action "%s" callable function should return at least an empty array',
        'ERROR_5' => 'Incorrect field of type "action_manager" definition in model config, action "%s" callable function should return an integer',
    );

    protected $availableActions = array('preSave','postSave','preDelete','postDelete','preSelect', 'postSelect');

    public function validate()
    {
        if (!is_array($this->definition)) {
            $this->errors[] = $this->errorMsgArray['ERROR_1'];
        } else {
            foreach ($this->definition as $action => $managerFunction) {
                if (!in_array($action, $this->availableActions)) {
                    $this->errors[] = sprintf($this->errorMsgArray['ERROR_2'], $action);
                } else {
                    if (!is_callable($managerFunction)) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_3'], $action);
                    }
                    switch ($action) {
                        case "postSelect":
                        case "preSave":
                            $result = $managerFunction(array(),null);
                            if (!is_array($result)) {
                                $this->errors[] = sprintf($this->errorMsgArray['ERROR_4'], $action);
                            }
                            break;
                        case "preDelete":
                        case "preSelect":
                            $result = $managerFunction(1,null);
                            if (!is_integer($result)) {
                                $this->errors[] = sprintf($this->errorMsgArray['ERROR_5'], $action);
                            }
                            break;
                        case "postSave":
                        case "postDelete":
                            break;
                    }
                }
            }
        }
        return parent::validate();
    }
}