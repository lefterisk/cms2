<?php
namespace Administration\Helper\Manager;

use Zend\Db\Sql\Select;

class TranslationManager extends AbstractManager
{
    protected $model;

    public function __construct(ModelManager $model)
    {
        $this->model = $model;
    }

    public function requiresTable()
    {
        if (count($this->model->getAllMultilingualFields()) > 0) {
            return true;
        }
        return false;
    }

    public function getTableName()
    {
        return $this->model->getTableName() . '_translation';
    }

    public function getTableColumnsDefinition()
    {
        $fields = array(
            'primary' => array($this->model->getPrefix() . 'id','language_id'),
            'varchar' => array_merge(
                $this->model->getMultilingualVarchars(),
                $this->model->getMultilingualFiles(),
                $this->model->getMultilingualFilesCaptions()
            ),
            'text'    => array_merge(
                $this->model->getMultilingualTexts(),
                $this->model->getMultilingualLongTexts()
            ),
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
            array('language_id',$this->model->getPrefix() . 'id'),
            $this->model->getMultilingualVarchars(),
            $this->model->getMultilingualFiles(),
            $this->model->getMultilingualFilesCaptions(),
            $this->model->getMultilingualTexts(),
            $this->model->getMultilingualLongTexts()
        );
        return $fields;
    }
}