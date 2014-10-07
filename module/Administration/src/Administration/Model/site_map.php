<?php
/**
 *Example configuration array for a model
 */
$modelHelper  = new \Administration\Helper\ModelHelper();
$SMOptionsArray = array(0 => '- Not a model -');
foreach ($this->modelHelper->getAvailableModels() as $model => $fullName) {
    if ($modelHelper->modelExists($model) && $model != 'site_map') {
        $models = $modelHelper->getAvailableModels();
        $modelDefinitionArray = require($models[$model]);
        if (is_array($modelDefinitionArray)) {
            $modelValidator = new \Administration\Helper\Validator\ModelValidator($modelDefinitionArray);
            if ($modelValidator->validate() && array_key_exists('stand_alone',$modelDefinitionArray) && $modelDefinitionArray['stand_alone']) {
                $SMOptionsArray[$model] = ucfirst(str_replace('_', ' ', $model));
            }
        }
    }
}

return array(
    "model"         => "site_map",
    "prefix"        => "site_map_",
    "stand_alone"   => false,
    "model_db_sync" => true,
    "fields" => array(
        "dates"                   => array(),
        "booleans"                => array(),
        "varchars"                => array('name','slug'),
        "texts"                   => array(),
        "long_texts"              => array(),
        "integers"                => array(),
        "files"                   => array(),
        "custom_selections"       => array(
            array(
                "name"              => "type",
                "options"           => array("0" => "Static", "1" => "Model"),
                "multiple"          => false,
            ),
            array(
                "name"              => "model",
                "options"           => $SMOptionsArray,
                "multiple"          => false,
            ),
        ),
        "relations" => array(

        ),
        "multilingual_varchars"   => array(),
        "multilingual_texts"      => array(),
        "multilingual_long_texts" => array(),
        "multilingual_files"      => array(),
    ),
    "max_tree_depth"  => 2,
    "listing_fields"  => array('name','status'),
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