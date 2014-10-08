<?php
/**
 * Configuration array for Site-map model
 */

//Create a list of all standalone models
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
        "varchars"                => array('name'),
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
        "multilingual_varchars"   => array('slug'),
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
        'preSave'    => function($data, $dbAdapter) {

            return $data;
        },
        'postSave'   => function($data, $dbAdapter) {
            $siteMapHelper = new \Administration\Helper\DbGateway\SiteMapHelper($dbAdapter,true);
            $siteMapHelper->saveRoutes($data);
        },
        'preSelect'  => function($id, $dbAdapter) {
            return $id;
        },
        'postSelect' => function($data, $dbAdapter) {
            return $data;
        },
        'preDelete'  => function($id, $dbAdapter) {
            return $id;
        },
        'postDelete' => function($id, $dbAdapter) {
                $siteMapHelper = new \Administration\Helper\DbGateway\SiteMapHelper($dbAdapter,true);
                $siteMapHelper->deleteRoutes($id);
        },
    )
);