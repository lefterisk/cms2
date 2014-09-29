<?php
namespace Administration\Helper\DbGateway;


class ParentLookupTable extends AbstractTable
{
    public function save($id, $mainModelForeignKey, $parent_field_name, $values = array())
    {
        $this->delete($id, $mainModelForeignKey);
        if (is_array($values) && !empty($values)) {
            foreach ($values as $value) {
                $this->getTableGateway()->insert(array(
                    $mainModelForeignKey => $id,
                    $parent_field_name   => $value
                ));
            }
        }
    }

    public function delete($id, $mainModelForeignKey)
    {
        $this->getTableGateway()->delete(array($mainModelForeignKey => $id));
    }
}