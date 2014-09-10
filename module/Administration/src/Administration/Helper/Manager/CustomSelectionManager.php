<?php
namespace Administration\Helper\Manager;


class CustomSelectionManager extends AbstractManager
{
    protected $definition;
    protected $model_prefix;
    protected $related_model_prefix;

    public function __construct(Array $definition, $model_prefix)
    {
        $this->definition           = $definition;
        $this->model_prefix         = $model_prefix;
    }

    public function requiresTable()
    {
        if ($this->definition['multiple']) {
            return true;
        }
        return false;
    }

    public function requiresColumn()
    {
        if (!$this->definition['multiple']) {
            return true;
        }
        return false;
    }

    public function getOptionsForSelect()
    {
        return $this->definition['options'];
    }

    public function getTableName()
    {
        return $this->definition['lookup_table_name'];
    }

    public function getColumn()
    {
        return $this->definition['name'];
    }

    public function getFieldName()
    {
        return $this->getColumn();
    }

    public function getTableColumnsDefinition()
    {
        return array(
            $this->model_prefix . 'id' => 'integer',
            $this->getColumn() => 'integer'
        );
    }

    public function getTableExchangeArray()
    {
        return array(
            $this->model_prefix . 'id',
            $this->getColumn()
        );
    }

    public function getInputFilter($inputFilter = null)
    {
        $inputFilter = parent::getInputFilter($inputFilter);
        $inputFilter->add(array(
            'name' => $this->getColumn(),
            'required' => false,
        ));
        return $inputFilter;
    }
}