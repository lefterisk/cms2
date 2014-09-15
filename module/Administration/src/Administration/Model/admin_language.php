<?php
/**
 *Example configuration array for a model
 */
return array(
    "model"         => "admin_language",
    "prefix"        => "admin_language_",
    "stand_alone"   => false,
    "model_db_sync" => true,
    "fields" => array(
        "dates"                   => array(),
        "booleans"                => array('status','is_primary'),
        "varchars"                => array('name','code'),
        "texts"                   => array(),
        "long_texts"              => array(),
        "integers"                => array(),
        "files"                   => array(),
        "custom_selections"       => array(),
        "relations"               => array(),
        "multilingual_varchars"   => array(),
        "multilingual_texts"      => array(),
        "multilingual_long_texts" => array(),
        "multilingual_files"      => array(),
    ),
    "max_tree_depth"  => 0,
    "listing_fields"  => array('name','code','status','is_primary'),
    "form_manager"    => array(

    ),
    "input_filters"   => array(
        array(
            "name" => "name",
            "required" => true
        ),
        array(
            "name" => "code",
            "required" => true
        )
    ),
    "action_manager" => array()
);