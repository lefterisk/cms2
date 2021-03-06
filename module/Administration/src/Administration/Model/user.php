<?php
/**
 *Configuration array for User model
 */
return array(
    "model"         => "user",
    "prefix"        => "user_",
    "stand_alone"   => false,
    "model_db_sync" => true,
    "fields" => array(
        "dates"                   => array(),
        "booleans"                => array('status'),
        "varchars"                => array('email','password','name','surname'),
        "texts"                   => array(),
        "long_texts"              => array(),
        "integers"                => array(),
        "files"                   => array(),
        "custom_selections"       => array(

        ),
        "relations" => array(
            "field_name" => array(
                "related_model"     => "user_group",
                "relation_type"     => "manyToOne", // 'oneToMany', 'manyToOne', 'manyToMany'
                "fields_for_select" => array("name"),
                "lookup_table_name" => "",
            ),
        ),
        "multilingual_varchars"   => array(),
        "multilingual_texts"      => array(),
        "multilingual_long_texts" => array(),
        "multilingual_files"      => array(),
    ),
    "max_tree_depth"  => 0,
    "listing_fields"  => array('email','status'),
    "form_manager"    => array(
//        "tab_name" => array(
//            "field_name"
//        )
    ),
    "input_filters"   => array(

    ),
    "action_manager" => array(
        'preSave'    => function($data) {
            if (array_key_exists('password', $data) && empty($data['password'])) {
                unset($data['password']);
            } elseif (array_key_exists('password', $data) && !empty($data['password'])) {
                $bCrypt           = new \Zend\Crypt\Password\Bcrypt();
                $data['password'] = $bCrypt->create($data['password']);
            }
            return $data;
        },
        'postSelect' => function($data) {
            if (array_key_exists('password', $data)) {
                unset($data['password']);
            }
            return $data;
        },
    )
);