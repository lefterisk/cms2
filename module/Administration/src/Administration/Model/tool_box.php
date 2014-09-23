<?php
/**
 *Configuration array for User model
 */
$optionsArray = array();
foreach ($this->modelHelper->getAvailableModels() as $model => $fullName) {
    $optionsArray[$model] = ucfirst(str_replace('_', ' ', $model));
}

return array(
    "model"         => "tool_box",
    "prefix"        => "tool_box_",
    "stand_alone"   => false,
    "model_db_sync" => true,
    "fields" => array(
        "dates"                   => array(),
        "booleans"                => array('status'),
        "varchars"                => array('name'),
        "texts"                   => array(),
        "long_texts"              => array(),
        "integers"                => array(),
        "files"                   => array(),
        "custom_selections"       => array(
            array(
                "name"              => "models",
                "options"           => $optionsArray,
                "multiple"          => true,
                "lookup_table_name" => "model_to_tool_box"
            ),
        ),
        "relations" => array(
            "field_name" => array(
                "related_model"     => "user_group",
                "relation_type"     => "manyToMany", // 'oneToMany', 'manyToOne', 'manyToMany'
                "fields_for_select" => array("name"),
                "lookup_table_name" => "tool_box_to_user_group",
            ),
        ),
        "multilingual_varchars"   => array(),
        "multilingual_texts"      => array(),
        "multilingual_long_texts" => array(),
        "multilingual_files"      => array(),
    ),
    "max_tree_depth"  => 0,
    "listing_fields"  => array('name','status'),
    "form_manager"    => array(

    ),
    "input_filters"   => array(

    ),
    "action_manager" => array(

    )
);