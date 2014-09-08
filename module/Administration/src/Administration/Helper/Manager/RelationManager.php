<?php
namespace Administration\Helper\Manager;

class RelationManager extends AbstractManager
{
    protected $definition;
    protected $model_prefix;
    protected $related_model_prefix;

    public function __construct(Array $definition, $model_prefix, $related_model_prefix)
    {
        $this->definition           = $definition;
        $this->model_prefix         = $model_prefix;
        $this->related_model_prefix = $related_model_prefix;
    }

    public function requiresTable()
    {
        if ($this->definition['relation_type'] == 'manyToMany') {
            return true;
        }
        return false;
    }

    public function requiresColumn()
    {
        if ($this->definition['relation_type'] == 'manyToOne') {
            return true;
        }
        return false;
    }

    public function getFieldsToReturn()
    {
        return $this->definition['fields_for_select'];
    }

    public function getTableName()
    {
        return $this->definition['lookup_table_name'];
    }

    public function getColumn()
    {
        return $this->related_model_prefix . 'id';
    }

    public function getFieldName()
    {
        return $this->getColumn();
    }

    public function getTableColumnsDefinition()
    {
        return array(
            $this->model_prefix . 'id' => 'integer',
            $this->related_model_prefix . 'id' => 'integer'
        );
    }

    public function getTableExchangeArray()
    {
        return array(
            $this->model_prefix . 'id',
            $this->related_model_prefix . 'id'
        );
    }
}