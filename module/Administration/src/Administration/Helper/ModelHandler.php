<?php
namespace Administration\Helper;

use Administration\Helper\Validator\ModelValidator;
use Zend\Code\Scanner\DirectoryScanner;

class ModelHandler
{
    private $errors               = array();
    private $initialised          = false;
    private $availableModelsArray = array();

    private $model = '';
    private $prefix = '';
    private $stand_alone = false;
    private $max_tree_depth = 0;
    private $listing_fields = array();
    private $required_fields = array();
    private $input_filters = array();
    private $action_manager;

    private $booleans = array();
    private $dates = array();
    private $files = array();
    private $varchars = array();
    private $texts = array();
    private $integers = array();
    private $long_texts = array();
    private $multilingual_varchars = array();
    private $multilingual_texts = array();
    private $multilingual_long_texts = array();
    private $multilingual_files = array();
    private $relations = array();
    private $custom_selections = array();

    private $generalSettings = array(
        'model',
        'prefix',
        'stand_alone',
        'max_tree_depth',
        'listing_fields',
        'required_fields',
        'form_manager',
        'input_filters',
        'action_manager'
    );
    private $fieldTypes = array(
        'booleans',
        'dates',
        'files',
        'varchars',
        'texts',
        'integers',
        'long_texts',
        'multilingual_varchars',
        'multilingual_texts',
        'multilingual_long_texts',
        'multilingual_files',
        'relations',
        'custom_selections'
    );

    public function __construct($model)
    {
        if ($this->modelExists($model)) {
            $modelDefinitionArray = require($this->availableModelsArray[$model]);
        } else {
            $this->errors[] = 'The requested Model does not exist!';
            return;
        }

        if (!is_array($modelDefinitionArray)) {
            $this->errors[] = 'Model is missing definitions array!';
            return;
        }

        $modelValidator = new ModelValidator($modelDefinitionArray);

        if (!$modelValidator->validate()) {
            $this->errors = array_merge($this->errors, $modelValidator->getErrors());
            return;
        }

        $this->consumeConfigurationArray($modelDefinitionArray);
        var_dump($this);
        $this->initialised = true;
    }

    private function modelExists($model)
    {
        $modelsArray = $this->getAvailableModels();
        if (array_key_exists($model, $modelsArray)) {
            return true;
        } else {
            return false;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function isInitialised()
    {
        return $this->initialised;
    }

    private function consumeConfigurationArray($array)
    {
        foreach ($this->generalSettings as $setting) {
            if (array_key_exists($setting, $array)) {
                $this->{$setting} = $array[$setting];
            }
        }

        if (array_key_exists('fields', $array)) {
            foreach ($this->fieldTypes as $type) {
                if (array_key_exists($type, $array['fields'])) {
                    $this->{$type} = $array['fields'][$type];
                }
            }
        }
    }

    private function getAvailableModels()
    {
        if (empty($this->availableModelsArray)) {
            $scanner = new DirectoryScanner(__DIR__ . '/../Model/');
            $this->availableModelsArray = array();
            if (is_array($scanner->getFiles())) {
                foreach ($scanner->getFiles() as $model) {
                    $file = explode('/', $model);
                    $this->availableModelsArray[str_replace('.php', '', array_pop($file))] = $model;
                }
            }
        }
        return $this->availableModelsArray;
    }

    public function getModelName()
    {
        return $this->model;
    }

    public function getPublishedField()
    {
        return $this->getPrefix() . 'status';
    }

    public function getBooleans()
    {
        if (!in_array($this->getPublishedField(), $this->booleans)) {
            $this->booleans[] = $this->getPublishedField();
        }
        return $this->booleans;
    }

    public function getDates()
    {
        return $this->dates;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getFileCaptions()
    {
        $captions = array();
        for ($i=0; $i<count($this->files); $i++)
        {
            $captions[$i] = $this->files[$i] . "_caption";
        }
        return $captions;
    }

    public function getVarchars()
    {
        return $this->varchars;
    }

    public function getTexts()
    {
        return $this->texts;
    }

    public function getLongTexts()
    {
        return $this->long_texts;
    }

    public function getIntegers()
    {
        return $this->integers;
    }

    public function getMultilingualVarchars()
    {
        if ($this->isStandAlonePage() && !in_array($this->getPrefix() . 'meta_title', $this->multilingual_varchars))
        {
            array_push($this->multilingual_varchars, $this->getPrefix() . 'meta_title');
        }
        if ($this->isStandAlonePage() && !in_array($this->getPrefix() . 'meta_slug', $this->multilingual_varchars))
        {
            array_push($this->multilingual_varchars, $this->getPrefix() . 'meta_slug');
        }
        return $this->multilingual_varchars;
    }

    public function getMultilingualTexts()
    {
        if ($this->isStandAlonePage() && !in_array($this->getPrefix() . 'meta_description', $this->multilingual_texts))
        {
            array_push($this->multilingual_texts, $this->getPrefix() . 'meta_description');
        }
        return $this->multilingual_texts;
    }

    public function getMultilingualLongTexts()
    {
        return $this->multilingual_long_texts;
    }

    public function getMultilingualFiles()
    {
        return $this->multilingual_files;
    }

    public function getMultilingualFilesCaptions()
    {
        $captions = array();
        for ($i=0; $i<count($this->multilingual_files); $i++)
        {
            $captions[$i] = $this->multilingual_files[$i] . "_caption";
        }
        return $captions;
    }

    public function getRequiredFields()
    {
        return $this->required_fields;
    }

    public function getListingFields()
    {
        return $this->listing_fields;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    public function getCustomSelections()
    {
        return $this->custom_selections;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getAllNonMultilingualFields()
    {
        return array_merge(
            $this->booleans,
            $this->dates,
            $this->varchars,
            $this->texts,
            $this->long_texts,
            $this->integers,
            $this->custom_selections,
            $this->files
        );
    }

    public function getAllMultilingualFields()
    {
        return array_merge(
            $this->multilingual_varchars,
            $this->multilingual_texts,
            $this->multilingual_long_texts,
            $this->multilingual_files,
            $this->getFileCaptions(),
            $this->getMultilingualFilesCaptions()
        );
    }

    public function getSimpleFields()
    {
        $simpleFields = array_merge(
            $this->dates,
            $this->varchars,
            $this->booleans,
            $this->integers,
            $this->texts,
            $this->multilingual_varchars,
            $this->multilingual_texts
        );
        return $simpleFields;
    }

    public function getAdvancedFields()
    {
        return array_merge($this->long_texts, $this->multilingual_long_texts);
    }

    public function getAllFileFields()
    {
        return array_merge($this->files, $this->multilingual_files);
    }

    public function getAllFields()
    {
        return array_merge(
            $this->getSimpleFields(),
            $this->getAdvancedFields(),
            $this->getAllFileFields(),
            $this->getRelations(),
            $this->getCustomSelections()
        );
    }

    public function getMaximumTreeDepth()
    {
        return $this->max_tree_depth;
    }

    public function getActionManagers()
    {
        return $this->action_manager;
    }

    public function getInputFilters()
    {
        return $this->input_filters;
    }

    public function getMetaFields()
    {
        return array(
            $this->getPrefix() . 'meta_title',
            $this->getPrefix() . 'meta_slug',
            $this->getPrefix() . 'meta_description'
        );
    }

    public function isMultiLingual()
    {
        if (count($this->getAllMultilingualFields()) > 0 ) {
            return true;
        }
        return false;
    }

    public function isStandAlonePage()
    {
        return $this->stand_alone;
    }

}