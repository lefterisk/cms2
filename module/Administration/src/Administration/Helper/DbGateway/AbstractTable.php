<?php
namespace Administration\Helper\DbGateway;


class AbstractTable
{
    protected $tableGateway;
    protected $tableColumns;

    public function __construct( CmsTableGateway $tableGateway, $tableColumns = array(), $modelDbSync = false)
    {
        $this->tableGateway = $tableGateway;
        $this->tableColumns  = $tableColumns;
        if ($modelDbSync) {
            $this->syncModelWithDbTable();
        }
    }

    private function syncModelWithDbTable()
    {
        if (!$this->tableGateway->tableExists()) {
            $this->tableGateway->createTable($this->tableColumns);
        } else {
            $this->tableGateway->syncColumns($this->tableColumns);
        }
    }

    public function addColumns(Array $columns)
    {
        foreach ($columns as $column => $type) {
            $this->tableColumns[$type][] = $column;
        }
        $this->tableGateway->syncColumns($columns);
    }

    public function getTableGateway()
    {
        return $this->tableGateway;
    }

    public function getLastInsertValue()
    {
        return $this->getTableGateway()->getLastInsertValue();
    }
}