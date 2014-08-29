<?php
/**
 *Example configuration array for a model
 */
return array(
    "model"         => "site_language",
    "prefix"        => "site_language_",
    "stand_alone"   => false,
    "model_db_sync" => true,
    "fields" => array(
        "dates"                   => array(),
        "booleans"                => array('site_language_status','site_language_primary'),
        "varchars"                => array('site_language_name','site_language_code'),
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
    "listing_fields"  => array(),
    "form_manager"    => array(

    ),
    "input_filters"   => array(
        array(
            "name" => "site_language_name",
            "required" => true
        ),
        array(
            "name" => "site_language_code",
            "required" => true
        )
    ),
    "action_manager" => ""
);