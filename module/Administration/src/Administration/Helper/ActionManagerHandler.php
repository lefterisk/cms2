<?php
namespace Administration\Helper;



class ActionManagerHandler
{
    protected $definition;

    public function __construct($actionManagerDefinition)
    {
        $this->definition = $actionManagerDefinition;
    }

    public function getActionProcessedData($action, $data)
    {
        if (array_key_exists($action, $this->definition) && is_callable($this->definition[$action])) {
            return $this->definition[$action]($data);
        }
        return $data;
    }
}