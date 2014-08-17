<?php
/**
 *Example configuration array for a model
 */
return array(
    "model"         => "example",
    "prefix"        => "example_",
    "stand_alone"   => false,
    "model_db_sync" => true,
    "fields" => array(
        "dates"                   => array('pipes_date'),
        "booleans"                => array('pipes_boolean'),
        "varchars"                => array('pipes1','pipes2','pipes3','pipes4'),
        "texts"                   => array('pipes_text'),
        "long_texts"              => array('pipes_long_test'),
        "integers"                => array('pipes_integer'),
        "files"                   => array('pipes_file'),
        "custom_selections"       => array(
            "field_name" => array(
                "options"           => array("key" => "value"),
                "multiple"          => false,
                "lookup_table_name" => ""
            )
        ),
        "relations" => array(
            "field_name" => array(
                "related_model"     => "sss",
                "relation_type"     => "oneToMany", // 'oneToMany', 'manyToOne', 'manyToMany'
                "fields_for_select" => array(),
                "lookup_table_name" => "",
            ),
        ),
        "multilingual_varchars"   => array(),
        "multilingual_texts"      => array(),
        "multilingual_long_texts" => array(),
        "multilingual_files"      => array(),
    ),
    "max_tree_depth"  => 0,
    "listing_fields"  => array(),
    "required_fields" => array(),
    "form_manager"    => array(
        "tab_name" => array(
            "field_name"
        )
    ),
    "input_filters"   => array(),
    "action_manager" => ""
);