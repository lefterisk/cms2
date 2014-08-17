<?php
namespace Administration\Helper\DbGateway;


class ModelTable extends AbstractTable
{
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }
}