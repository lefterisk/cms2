<?php
/**
 *Configuration array for UserGroup model
 */
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
//            "field_name" => array(
//                "options"           => array("key" => "value"),
//                "multiple"          => false,
//                "lookup_table_name" => ""
//            )
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
    "action_manager" => ""
);