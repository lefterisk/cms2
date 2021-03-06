<?php
namespace Administration\Helper\Manager;


class ModelManager extends AbstractManager
{
    private $model = '';
    private $prefix = '';
    public static $published = 'status';
    private $stand_alone = false;
    private $model_db_sync = false;
    private $max_tree_depth = 0;
    private $listing_fields = array();
    private $input_filters = array();
    private $form_manager = array();
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

    //relation & custom selections fields
    private $relations_fields = array();
    private $custom_selection_varchar_fields = array();
    private $custom_selection_int_fields = array();

    private $generalSettings = array(
        'model',
        'prefix',
        'stand_alone',
        'model_db_sync',
        'max_tree_depth',
        'listing_fields',
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

    public function __construct(Array $array)
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

    public function getTableName()
    {
        return $this->model;
    }

    public function requiresTable()
    {
        return true;
    }

    public function getModelName()
    {
        return $this->model;
    }

    public function getPublishedField()
    {
        return ModelManager::$published;
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
        if ($this->isStandAlonePage() && !in_array($this->getPrefix() . 'meta_title', $this->multilingual_varchars)) {
            array_push($this->multilingual_varchars, $this->getPrefix() . 'meta_title');
        }
        if ($this->isStandAlonePage() && !in_array($this->getPrefix() . 'meta_slug', $this->multilingual_varchars)) {
            array_push($this->multilingual_varchars, $this->getPrefix() . 'meta_slug');
        }
        return $this->multilingual_varchars;
    }

    public function getMultilingualTexts()
    {
        if ($this->isStandAlonePage() && !in_array($this->getPrefix() . 'meta_description', $this->multilingual_texts)) {
            array_push($this->multilingual_texts, $this->getPrefix() . 'meta_description');
        }
        return $this->multilingual_texts;
    }

    public function getMetaFields()
    {
        return array(
            $this->getMetaTitleFieldName(),
            $this->getMetaSlugFieldName(),
            $this->getMetaDescriptionFieldName()
        );
    }

    public function getMetaTitleFieldName()
    {
        return $this->getPrefix() . 'meta_title';
    }

    public function getMetaSlugFieldName()
    {
        return $this->getPrefix() . 'meta_slug';
    }

    public function getMetaDescriptionFieldName()
    {
        return $this->getPrefix() . 'meta_description';
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
        for ($i=0; $i<count($this->multilingual_files); $i++) {
            $captions[$i] = $this->multilingual_files[$i] . "_caption";
        }
        return $captions;
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
            $this->getBooleans(),
            $this->getDates(),
            $this->getVarchars(),
            $this->getTexts(),
            $this->getLongTexts(),
            $this->getIntegers(),
            $this->getCustomSelections(),
            $this->getFiles()
        );
    }

    public function getAllMultilingualFields()
    {
        return array_merge(
            $this->getMultilingualVarchars(),
            $this->getMultilingualTexts(),
            $this->getMultilingualLongTexts(),
            $this->getMultilingualFiles(),
            $this->getMultilingualFilesCaptions()
        );
    }

    public function getSimpleFields()
    {
        $simpleFields = array_merge(
            $this->getDates(),
            $this->getVarchars(),
            $this->getBooleans(),
            $this->getIntegers(),
            $this->getTexts(),
            $this->getMultilingualVarchars(),
            $this->getMultilingualTexts()
        );
        return $simpleFields;
    }

    public function getAdvancedFields()
    {
        return array_merge($this->getLongTexts(), $this->getMultilingualLongTexts());
    }

    public function getAllFileFields()
    {
        return array_merge($this->getFiles(), $this->getMultilingualFiles());
    }

    public function getAllFields()
    {
        return array_merge(
            $this->getSimpleFields(),
            $this->getAdvancedFields(),
            $this->getAllFileFields()
        );
    }

    public function getTableColumnsDefinition()
    {
        $fields = array(
            'id'      => array('id'),
            'date'    => $this->getDates(),
            'varchar' => array_merge($this->getVarchars(), $this->getFiles(), $this->getFileCaptions(),$this->getCustomSelectionVarcharFields()),
            'boolean' => $this->getBooleans(),
            'integer' => array_merge($this->getIntegers(), $this->getRelationFields(),$this->getCustomSelectionIntFields()),
            'text'    => array_merge($this->getTexts(),$this->getLongTexts()),
        );

        $columnsWithTypes = array();
        foreach ($fields as $type => $columns) {
            foreach ($columns as $column) {
                $columnsWithTypes[$column] = $type;
            }
        }
        return $columnsWithTypes;
    }

    public function getTableExchangeArray()
    {
        $fields = array_merge(
            array('id'),
            $this->getRelationFields(),
            $this->getCustomSelectionVarcharFields(),
            $this->getCustomSelectionIntFields(),
            $this->getDates(),
            $this->getVarchars(),
            $this->getBooleans(),
            $this->getIntegers(),
            $this->getTexts(),
            $this->getLongTexts(),
            $this->getFiles(),
            $this->getFileCaptions()
        );
        return $fields;
    }

    public function getMaximumTreeDepth()
    {
        return $this->max_tree_depth;
    }

    public function getModelDbTableSync()
    {
        return $this->model_db_sync;
    }

    public function getActionManagers()
    {
        return $this->action_manager;
    }

    public function getInputFilter()
    {
        $inputFilter = parent::getInputFilter();
        //add model defined filters
        foreach ($this->input_filters as $filterDefinition) {
            $inputFilter->add($filterDefinition);
        }
        //add filters to all integer fields
        foreach ($this->getIntegers() as $integer) {
            $inputFilter->add(
                array(
                    'name' => $integer,
                    'required' => false,
                    'allowEmpty' => true,
                    'validators' => array(
                        array(
                            'name'  => 'Int',
                        )
                    ),
                )
            );
        }
        return $inputFilter;
    }

    public function getFormManager()
    {
        return $this->form_manager;
    }

    public function getRelationFields()
    {
        return $this->relations_fields;
    }

    public function getCustomSelectionIntFields()
    {
        return $this->custom_selection_int_fields;
    }

    public function getCustomSelectionVarcharFields()
    {
        return $this->custom_selection_varchar_fields;
    }

    public function setRelationField($field)
    {
        $this->relations_fields[] = $field;
    }

    public function setCustomSelectionVarcharField($field)
    {
        $this->custom_selection_varchar_fields[] = $field;
    }

    public function setCustomSelectionIntField($field)
    {
        $this->custom_selection_int_fields[] = $field;
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