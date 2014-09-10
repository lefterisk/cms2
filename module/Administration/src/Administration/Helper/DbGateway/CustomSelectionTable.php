<?php
namespace Administration\Helper\DbGateway;


class CustomSelectionTable extends AbstractTable
{
    public function save($id, $mainModelForeignKey, $customSelectionField, $values = array())
    {
        $this->delete($id, $mainModelForeignKey);
        if (is_array($values) && !empty($values)) {
            foreach ($values as $value) {
                $this->getTableGateway()->insert(array(
                    $mainModelForeignKey    => $id,
                    $customSelectionField   => $value
                ));
            }
        }
    }

    public function delete($id, $mainModelForeignKey)
    {
        $this->getTableGateway()->delete(array($mainModelForeignKey => $id));
    }
}