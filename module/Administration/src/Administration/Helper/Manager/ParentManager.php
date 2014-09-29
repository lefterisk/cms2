<?php
namespace Administration\Helper\Manager;


class ParentManager extends AbstractManager
{
    protected $model;

    public function __construct(ModelManager $model)
    {
        $this->model = $model;
    }

    public function requiresTable()
    {
        if ($this->model->getMaximumTreeDepth() > 0) {
            return true;
        }
        return false;
    }

    public function getTableName()
    {
        return $this->model->getTableName() . '_to_parent';
    }

    public function getFieldName()
    {
        return 'parent_id';
    }

    public function getTableColumnsDefinition()
    {
        $fields = array(
            'primary' => array($this->model->getPrefix() . 'id', $this->getFieldName()),
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
            array($this->getFieldName(), $this->model->getPrefix() . 'id')
        );
        return $fields;
    }
}