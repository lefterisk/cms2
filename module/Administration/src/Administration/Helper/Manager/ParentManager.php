<?php
namespace Administration\Helper\Manager;

use Zend\Db\Sql\Expression;

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
        $columnsWithTypes = array(
            $this->model->getPrefix() . 'id' => 'primary',
            $this->getFieldName() => 'primary',
            'depth' => 'int'
        );
        return $columnsWithTypes;
    }

    public function getTableExchangeArray()
    {
        $fields = array_merge(
            array($this->getFieldName(), $this->model->getPrefix() . 'id')
        );
        return $fields;
    }

    public function getParentTableJoinDefinition($parent = 0)
    {
        return array(
            array(
                'table_name'          => array('p' => $this->getTableName()),
                'on_field_expression' => 'p.' . $this->model->getPrefix() . 'id' . ' = ' . $this->model->getTableName() . '.id',
                'return_fields'       => array_merge($this->getTableSpecificListingFields(array('p.' . $this->getFieldName())), array('depth')),
                'where'               => array('p.'.$this->getFieldName() => $parent),
            ),
            array(
                'table_name'          => array('crumbs' => $this->getTableName()),
                'on_field_expression' => 'crumbs.' . $this->model->getPrefix() . 'id' . ' = ' . $this->model->getTableName() . '.id',
                'return_fields'       => array('breadcrumbs' => new Expression(" GROUP_CONCAT( crumbs.`". $this->getFieldName() ."` ORDER BY crumbs.depth DESC SEPARATOR ',' ) ")),
                'where'               => array()
            ),
        );
    }
}