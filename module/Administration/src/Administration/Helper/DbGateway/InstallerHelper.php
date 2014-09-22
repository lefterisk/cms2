<?php
namespace Administration\Helper\DbGateway;


use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\Exception;


class InstallerHelper
{
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function databaseExists()
    {
        try {
            $this->adapter->getCurrentSchema();
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function modelTableExists($model)
    {
        $statement = $this->adapter->createStatement('SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = "' . $this->adapter->getCurrentSchema() . '" AND table_name ="' . $model . '"');
        $result    = $statement->execute();
        if ($result->count()==0)
        {
            return false;
        }
        return true;
    }

    public function createTable($definition)
    {
        $statement = $this->adapter->createStatement($definition);
        $result    = $statement->execute();
        if ($result->count()==0)
        {
            return false;
        }
        return true;
    }
}