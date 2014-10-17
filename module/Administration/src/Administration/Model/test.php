<?php
/**
 *Example configuration array for a model
 */
return array(
    "model"         => "test",
    "prefix"        => "test_",
    "stand_alone"   => true,
    "model_db_sync" => true,
    "fields" => array(
        "dates"                   => array(),
        "booleans"                => array(),
        "varchars"                => array('pipes1'),
        "texts"                   => array(),
        "long_texts"              => array(),
        "integers"                => array(),
        "files"                   => array(),
        "relations" => array(
            "field_name" => array(
                "related_model"     => "example",
                "relation_type"     => "manyToOne", // 'oneToMany', 'manyToOne', 'manyToMany'
                "fields_for_select" => array('pipes1'),
            ),
        ),
        "multilingual_varchars"   => array(),
        "multilingual_texts"      => array(),
        "multilingual_long_texts" => array(),
        "multilingual_files"      => array(),
    ),
    "max_tree_depth"  => 1,
    "listing_fields"  => array('pipes1','status'),
    "form_manager"    => array(
//        "tab_name" => array(
//            "field_name"
//        )
    ),
    "input_filters"   => array(

    ),
    "action_manager" => array(
        'preSave'    => function($data) {

            return $data;
        },
        'postSave'   => function($data) {

        },
        'preSelect'  => function($id) {
            return $id;
        },
        'postSelect' => function($data) {
            return $data;
        },
        'preDelete'  => function($id) {
            return $id;
        },
        'postDelete' => function($id) {

        },
    )
);