<?php
namespace Administration\Helper;

use Zend\Db\Adapter\Adapter;

class ActionManagerHandler
{
    protected $definition;
    protected $dbAdapter;

    public function __construct($actionManagerDefinition, Adapter $dbAdapter)
    {
        $this->definition = $actionManagerDefinition;
        $this->dbAdapter  = $dbAdapter;
    }

    public function getActionProcessedData($action, $data)
    {
        if (array_key_exists($action, $this->definition) && is_callable($this->definition[$action])) {
            //pass the adapter to the action function in case db interaction is required
            return $this->definition[$action]($data, $this->dbAdapter);
        }
        return $data;
    }
}