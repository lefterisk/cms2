<?php
namespace Administration\Helper\Manager;

abstract class  AbstractManager
{
    abstract public function getTableName();
    abstract public function requiresTable();
    abstract public function getTableColumnsDefinition();
    abstract public function getTableExchangeArray();
    public function requiresColumn()
    {
        return false;
    }
    public function getColumn()
    {
        return false; //string column_name
    }
}