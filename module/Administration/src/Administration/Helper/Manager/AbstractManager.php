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

    public function getTableSpecificListingFields(Array $return_fields)
    {
        $fields = array();
        foreach ($return_fields as $field) {
            if (in_array($field, $this->getTableExchangeArray())) {
                $fields[] = $field;
            }
        }
        return $fields;
    }
}