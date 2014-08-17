<?php
namespace Administration\Helper\DbGateway;


class AbstractTable
{
    protected $tableGateway;
    protected $tableColumns;

    public function __construct( CmsTableGateway $tableGateway, Array $tableColumns, $modelDbSync = false)
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
}