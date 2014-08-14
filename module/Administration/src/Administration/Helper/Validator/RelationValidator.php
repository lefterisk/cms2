<?php
namespace Administration\Helper\Validator;

/**
 * Class RelationValidator
 * @package Administration\Helper\Validator
 *
 * Validates Relations Fields array
 *
 * Proper definition:
 * array(
 *    "field_name_1" => array(
 *        "related_model"     => "related_model_1", <-- Required
 *        "relation_type"     => "oneToMany", // 'oneToMany', 'manyToOne', 'manyToMany' <-- Required
 *        "fields_for_select" => array("field_1","field_2",...),
 *        "lookup_table_name" => "", <-- if relation type is 'manyToOne' this is required
 *    ),
 *    "field_name_2" => array(
 *        "related_model"     => "related_model_2", <-- Required
 *        "relation_type"     => "oneToMany", // 'oneToMany', 'manyToOne', 'manyToMany' <-- Required
 *        "fields_for_select" => array("field_1","field_2",...),
 *        "lookup_table_name" => "", <-- if relation type is 'manyToOne' this is required
 *    ),
 *    .....
 * ),
 */
class RelationValidator extends AbstractValidator
{
    protected $errorMsgArray = array(
        'ERROR_1' => 'Incorrect field type "relations" definition in model config, should be an array!',
        'ERROR_2' => 'Incorrect field of type "relations" definition in model config, each definition array requires a key that contains the properly formed name of the field!',
        'ERROR_3' => 'Incorrect field of type "relations" definition in model config, should be an array!',
        'ERROR_4' => 'Incorrect field "%s" of type "relations" definition in model config, parameter "related_model" is required and needs to be a string!',
        'ERROR_5' => 'Incorrect field "%s" of type "relations" definition in model config, parameter "fields_for_select" is required and needs to be an array!',
        'ERROR_6' => 'Incorrect field "%s" of type "relations" definition in model config, "fields_for_select" contents must be properly formed strings!',
        'ERROR_7' => 'Incorrect field "%s" of type "relations" definition in model config, parameter "relation_type" is required and needs to be one of: "oneToMany", "manyToOne", "manyToMany" !',
        'ERROR_8' => 'Incorrect field "%s" of type "relations" definition in model config, if parameter "relation_type" is "manyToMany" you need to provide a "lookup_table_name" definition',
        'ERROR_9' => 'Incorrect field "%s" of type "relations" definition in model config, parameter "lookup_table_name" must be a properly formed table name!'
    );

    public function validate()
    {
        if (!is_array($this->definition)) {
            $this->errors[] = $this->errorMsgArray['ERROR_1'];
        } else {
            foreach ($this->definition as $field => $definitionArray) {
                if (!$this->isValidVariableName($field)) {
                    $this->errors[] = $this->errorMsgArray['ERROR_2'];
                }
                if (!is_array($definitionArray)) {
                    $this->errors[] = $this->errorMsgArray['ERROR_3'];
                } else {
                    if (!array_key_exists( 'related_model', $definitionArray) || !$this->isValidVariableName($definitionArray['related_model'])) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_4'], $field);
                    }
                    if (!array_key_exists( 'fields_for_select', $definitionArray) || !is_array($definitionArray['fields_for_select'])) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_5'], $field);
                    } else {
                        foreach ($definitionArray['fields_for_select'] as $field) {
                            if (!$this->isValidVariableName($field)) {
                                $this->errors[] = sprintf($this->errorMsgArray['ERROR_6'], $field);
                            }
                        }
                    }
                    if (!array_key_exists( 'relation_type', $definitionArray) || empty($definitionArray['relation_type']) || !in_array($definitionArray['relation_type'], array('oneToMany', 'manyToOne', 'manyToMany'))) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_7'], $field);
                    } elseif ($definitionArray['relation_type'] == 'manyToMany' && !array_key_exists( 'lookup_table_name', $definitionArray)) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_8'], $field);
                    }
                    if (array_key_exists( 'lookup_table_name', $definitionArray) && $definitionArray['relation_type'] == 'manyToMany' && (empty($definitionArray['lookup_table_name']) || !$this->isValidVariableName($definitionArray['lookup_table_name']))) {
                        $this->errors[] = sprintf($this->errorMsgArray['ERROR_9'], $field);
                    }
                }
            }
        }
        return parent::validate();
    }
}