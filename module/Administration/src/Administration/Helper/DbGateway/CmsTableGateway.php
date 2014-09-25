<?php
namespace Administration\Helper\DbGateway;


use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;


class CmsTableGateway extends TableGateway
{
    public function tableExists()
    {
        $statement = $this->adapter->createStatement('SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = "' . $this->adapter->getCurrentSchema() . '" AND table_name ="' . $this->getTable() . '"');
        $result    = $statement->execute();
        if ($result->count()==0)
        {
            return false;
        }
        return true;
    }

    public function createTable(Array $columns)
    {
        $sqlPartial    = '';
        $primaryKeys   = '';
        foreach ($columns as $column => $type){
            if (in_array($type,array('id','primary'))) {
                $primaryKeys .= '`' . $column . '`,';
            }
            $sqlPartial .= $this->getTableColumnPartialSql($column, $type).',';
        }
        if ($primaryKeys) {
            $sqlPartial = $sqlPartial . ' PRIMARY KEY (' . rtrim($primaryKeys, ',') . ')';
        } else {
            $sqlPartial  = rtrim($sqlPartial, ',');
        }
        $this->adapter->query('CREATE TABLE IF NOT EXISTS `' . $this->getTable() . '` (' . $sqlPartial .')', Adapter::QUERY_MODE_EXECUTE);
    }

    public function syncColumns(Array $columns)
    {
        foreach ($columns as $column => $type) {
            if (!$this->tableColumnExists($column)) {
                $this->addTableColumn($column, $type);
            }
        }
    }

    private function tableColumnExists($column)
    {
        $statement = $this->adapter->createStatement("SHOW COLUMNS FROM " . $this->getTable() . " LIKE '" . $column . "'" );
        $result = $statement->execute();
        if ($result->count()==0)
        {
            return false;
        }
        return true;
    }

    private function addTableColumn($name, $type)
    {
        $this->adapter->query("ALTER TABLE " . $this->getTable() . " ADD " . $this->getTableColumnPartialSql($name, $type) .";", Adapter::QUERY_MODE_EXECUTE);
    }

    private function getTableColumnPartialSql($name, $type)
    {
        switch($type){
            case 'id':
                $fieldType = " INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ";
                break;
            case 'primary':
                $fieldType = " INT(11) UNSIGNED NOT NULL ";
                break;
            case 'integer':
                $fieldType = " INT(11) ";
                break;
            case 'boolean':
                $fieldType = " ENUM( '0', '1' ) NOT NULL ";
                break;
            case 'date':
                $fieldType = " VARCHAR( 25 ) ";
                break;
            case 'text':
                $fieldType = " TEXT ";
                break;
            case 'varchar':
                $fieldType = " VARCHAR( 255 ) ";
                break;
            default:
                $fieldType = " VARCHAR( 255 ) ";
                break;
        }
        return "`" . $name . "`" . $fieldType;
    }

    public function setColumns(Array $columns)
    {
        $this->columns = $columns;
    }
}