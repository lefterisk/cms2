<?php
/**
 *Example configuration array for a model
 */
return array(
    "model_name"     => "Example",
    "table_name"     => "example",
    "prefix"         => "example_",
    "is_stand_alone" => false,
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
                "related_model"     => "",
                "relation_type"     => "", // 'oneToMany', 'manyToOne', 'manyToMany'
                "fields_for_select" => array(),
                "lookup_table_name" => "",
            )
        ),
        "multilingual_varchars"   => array(),
        "multilingual_texts"      => array(),
        "multilingual_long_texts" => array(),
        "multilingual_files"      => array(),
    ),
    "max_tree_depth"  => 0,
    "listing_fields"  => array(),
    "required_fields" => array(),
    "form_manager"    => array(),
    "input_filters"   => array()
);