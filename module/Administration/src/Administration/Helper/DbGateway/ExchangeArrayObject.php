<?php
namespace Administration\Helper\DbGateway;

/**
 * Class ExchangeArrayObject
 * @package Administration\Helper\DbGateway
 * Dummy object to satisfy Zend\Db\ResultSet\ResultSet that requires an object implementing a method exchangeArray
 */
class ExchangeArrayObject
{
    private $properties = array();

    public function __construct(Array $properties)
    {
        $this->properties = $properties;
    }

    public function addProperty($property)
    {
        $this->properties[] = $property;
    }

    public function exchangeArray($data)
    {
        foreach ($this->properties as $property) {
            $this->{$property} = (isset($data[$property])) ? $data[$property] : null;
        }
    }
}