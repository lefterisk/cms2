<?php
namespace Administration\Helper\Manager;

class TranslationManager
{
    protected $requiresTable = false;
    protected $tableName = '';
    protected $model;

    public function __construct(ModelManager $model)
    {
        $this->model = $model;
    }

    public function requiresTable()
    {
        return $this->requiresTable;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getTableColumns()
    {

    }

    public function getTableExchangeArrayFields()
    {

    }
}