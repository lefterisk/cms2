<?php
namespace Administration\Helper\DbGateway;


class RelationTable extends AbstractTable
{
    public function save($id, $mainModelForeignKey, $relatedModelForeignKey, $values = array())
    {
        $this->delete($id, $mainModelForeignKey);
        if (is_array($values) && !empty($values)) {
            foreach ($values as $value) {
                $this->getTableGateway()->insert(array(
                    $mainModelForeignKey    => $id,
                    $relatedModelForeignKey => $value
                ));
            }
        }
    }

    public function delete($id, $mainModelForeignKey)
    {
        $this->getTableGateway()->delete(array($mainModelForeignKey => $id));
    }
}