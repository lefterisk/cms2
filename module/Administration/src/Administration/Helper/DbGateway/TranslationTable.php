<?php
namespace Administration\Helper\DbGateway;


class TranslationTable extends AbstractTable
{
    public function save(Array $fieldValues, $foreignKeysArray = array())
    {
        if (is_array($foreignKeysArray) && !empty($foreignKeysArray)) {
            $this->getTableGateway()->update($fieldValues, $foreignKeysArray);
        } else {
            $this->getTableGateway()->insert($fieldValues);
        }
    }
}