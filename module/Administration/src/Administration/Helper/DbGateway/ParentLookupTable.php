<?php
namespace Administration\Helper\DbGateway;


use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;

class ParentLookupTable extends AbstractTable
{
    public function insert($id, $mainModelForeignKey, $parent_field_name, $value)
    {
        $sql = "INSERT INTO " . $this->getTableGateway()->getTable() . " (" . $parent_field_name . ", " . $mainModelForeignKey . ", depth) SELECT " . $parent_field_name.", ? , depth+1 FROM " . $this->getTableGateway()->getTable() . " WHERE " . $mainModelForeignKey . " = ? UNION ALL SELECT ?, ? , 0";
        $this->getTableGateway()->getAdapter()->query($sql,array($id,$value,$id,$id));
    }

    public function update($id, $mainModelForeignKey, $parent_field_name, $value)
    {
        $this->deleteForUpdate($id, $mainModelForeignKey,$parent_field_name);
//        INSERT INTO TreePaths (ancestor, descendant, length)
//        SELECT supertree.ancestor, subtree.descendant,
//        supertree.length+subtree.length+1
//        FROM TreePaths AS supertree JOIN TreePaths AS subtree
//        WHERE subtree.ancestor = 'D'
//        AND supertree.descendant = 'B';

        
//        $sql = "INSERT INTO " . $this->getTableGateway()->getTable() . " (" . $parent_field_name . ", " . $mainModelForeignKey . ", depth) SELECT " . $parent_field_name.", ? , depth+1 FROM " . $this->getTableGateway()->getTable() . " WHERE " . $mainModelForeignKey . " = ? UNION ALL SELECT ?, ? , 0";
//        $this->getTableGateway()->getAdapter()->query($sql,array($id,$value,$id,$id));
    }

    public function save($id, $mainModelForeignKey, $parent_field_name, $value)
    {
        $this->delete($id, $mainModelForeignKey);
        array(
            $mainModelForeignKey => $id,
            $parent_field_name   => $value
        );

        $sql = "INSERT INTO " . $this->getTableGateway()->getTable() . " (" . $parent_field_name . ", " . $mainModelForeignKey . ", depth) SELECT " . $parent_field_name.", ? , depth+1 FROM " . $this->getTableGateway()->getTable() . " WHERE " . $mainModelForeignKey . " = ? UNION ALL SELECT ?, ? , 0";
        $this->getTableGateway()->getAdapter()->query($sql,array($id,$value,$id,$id));
    }

    public function deleteForUpdate($id, $mainModelForeignKey,$parent_field_name)
    {
        $sql = "DELETE a FROM " . $this->getTableGateway()->getTable() . " AS a JOIN " . $this->getTableGateway()->getTable() . " AS d ON a." . $mainModelForeignKey . " = d." . $mainModelForeignKey . " LEFT JOIN " . $this->getTableGateway()->getTable() . " AS x ON x." . $parent_field_name . " = d." . $parent_field_name . " AND x." . $mainModelForeignKey . " = a." . $parent_field_name . " WHERE d." . $parent_field_name . " = '?' AND x." . $parent_field_name . " IS NULL;";
        $this->getTableGateway()->getAdapter()->query($sql,array($id));
    }

    public function getParentId($id, $mainModelForeignKey, $parent_field_name)
    {
        $result = $this->getTableGateway()->select(array($mainModelForeignKey => $id, 'depth' => 1))->current();
        return $result[$parent_field_name];
    }
}