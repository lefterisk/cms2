<?php
/**
 *Example configuration array for a model
 */
return array(
    "model"       => "example",
    "prefix"      => "example_",
    "stand_alone" => false,
    "fields" => array(
        "dates"                   => array(),
        "booleans"                => array(),
        "varchars"                => array(),
        "texts"                   => array(),
        "long_texts"              => array(),
        "integers"                => array(),
        "files"                   => array(),
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