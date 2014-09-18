<?php
/**
 *Configuration array for UserGroup model
 */

$optionsArray = array();
foreach ($this->modelHelper->getAvailableModels() as $model => $fullName) {
    $optionsArray[$model] = ucfirst(str_replace('_', ' ', $model));
}

return array(
    "model"         => "user_group",
    "prefix"        => "user_group_",
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
                "name"              => "view_models",
                "options"           => $optionsArray,
                "multiple"          => true,
                "lookup_table_name" => "user_group_to_view_models"
            ),
            array(
                "name"              => "add_models",
                "options"           => $optionsArray,
                "multiple"          => true,
                "lookup_table_name" => "user_group_to_add_models"
            ),
            array(
                "name"              => "edit_models",
                "options"           => $optionsArray,
                "multiple"          => true,
                "lookup_table_name" => "user_group_to_edit_models"
            ),
            array(
                "name"              => "delete_models",
                "options"           => $optionsArray,
                "multiple"          => true,
                "lookup_table_name" => "user_group_to_delete_models"
            )
        ),
        "relations" => array(

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
    "action_manager" => array()
);