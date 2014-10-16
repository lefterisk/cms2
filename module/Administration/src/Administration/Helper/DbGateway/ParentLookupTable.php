<?php
namespace Administration\Helper\DbGateway;


class ParentLookupTable extends AbstractTable
{
    public function save($id, $mainModelForeignKey, $parent_field_name, $value)
    {
        $result = $this->getTableGateway()->select(array($mainModelForeignKey => $id, $parent_field_name => $id, 'depth' => 0 ));
        if ($result->count() > 0) {
            $this->update($id, $mainModelForeignKey, $parent_field_name, $value);
        } else {
            $this->insert($id, $mainModelForeignKey, $parent_field_name, $value);
        }
    }

    public function insert($id, $mainModelForeignKey, $parent_field_name, $value)
    {
        //todo convert this query to ZF2 query
        $sql = "INSERT INTO " . $this->getTableGateway()->getTable() . " (" . $parent_field_name . ", " . $mainModelForeignKey . ", depth) SELECT " . $parent_field_name.", ? , depth+1 FROM " . $this->getTableGateway()->getTable() . " WHERE " . $mainModelForeignKey . " = ? UNION ALL SELECT ?, ? , 0";
        $this->getTableGateway()->getAdapter()->query($sql, array($id, $value, $id, $id));
    }

    public function update($id, $mainModelForeignKey, $parent_field_name, $value)
    {
//        INSERT `prefix_nodes_paths` (
//            `ancestor_id`,
//            `descendant_id`,
//            `path_length`
//        )
//        SELECT
//            supertree.`ancestor_id`,
//            subtree.`descendant_id`,
//            supertree.`path_length` + subtree.`path_length` + 1
//        FROM
//            `prefix_nodes_paths` AS supertree
//            JOIN `prefix_nodes_paths` AS subtree
//        WHERE subtree.`ancestor_id` = `node_old_parent_id`
//        AND supertree.`descendant_id` = `node_new_parent_id` ;


        $this->deleteForUpdate($id, $mainModelForeignKey, $parent_field_name);
        //todo convert this query to ZF2 query
        $sql = "INSERT INTO " . $this->getTableGateway()->getTable() . " (" . $parent_field_name . ", " . $mainModelForeignKey . ", depth) ".
               "SELECT supertree." . $parent_field_name . ", subtree." . $mainModelForeignKey . " , supertree.depth + subtree.depth + 1 ".
               "FROM " . $this->getTableGateway()->getTable() . " AS supertree ".
               "JOIN " . $this->getTableGateway()->getTable() . " AS subtree ".
               "WHERE subtree." . $parent_field_name . " = ? " .
               "AND supertree." . $mainModelForeignKey . " = ? ";
        $this->getTableGateway()->getAdapter()->query($sql, array($id, $value));
    }

    public function deleteForUpdate($id, $mainModelForeignKey, $parent_field_name)
    {
//        DELETE
//            a
//        FROM
//            `prefix_nodes_paths` AS a
//            JOIN `prefix_nodes_paths` AS d ON a.`descendant_id` = d.`descendant_id`
//            LEFT JOIN `prefix_nodes_paths` AS x ON x.`ancestor_id` = d.`ancestor_id`
//        AND x.`descendant_id` = a.`ancestor_id`
//        WHERE d.`ancestor_id` = `node_old_parent_id`
//        AND x.`ancestor_id` IS NULL ;

        //todo convert this query to ZF2 query
        $sql = "DELETE a FROM " . $this->getTableGateway()->getTable() . " AS a ".
               "JOIN " . $this->getTableGateway()->getTable() . " AS d ON a." . $mainModelForeignKey . " = d." . $mainModelForeignKey . " ".
               "LEFT JOIN " . $this->getTableGateway()->getTable() . " AS x ON x." . $parent_field_name . " = d." . $parent_field_name . " ".
               "AND x." . $mainModelForeignKey . " = a." . $parent_field_name . " ".
               "WHERE d." . $parent_field_name . " = '?' ".
               "AND x." . $parent_field_name . " IS NULL;";
        $this->getTableGateway()->getAdapter()->query($sql, array($id));
    }

    public function delete($id, $mainModelForeignKey, $parent_field_name)
    {
//        UPDATE
//        `prefix_nodes` AS d
//        JOIN `prefix_nodes_paths` AS p ON d.`id` = p.`descendant_id`
//        JOIN `prefix_nodes_paths` AS crumbs ON crumbs.`descendant_id` = p.`descendant_id`
//        SET d.`is_deleted` = deleted
//        WHERE p.`ancestor_id` = node_id;

        $sql = "DELETE d FROM " . $this->getTableGateway()->getTable() . " AS d " .
               "JOIN " . $this->getTableGateway()->getTable() . " AS p ON d." . $mainModelForeignKey . " = p." . $mainModelForeignKey . " ".
               "JOIN " . $this->getTableGateway()->getTable() . " AS crumbs ON crumbs." . $mainModelForeignKey . " = p." . $mainModelForeignKey . " ".
               "WHERE p." . $parent_field_name . " = '?' ";
        $this->getTableGateway()->getAdapter()->query($sql, array($id));
    }

    public function getParentId($id, $mainModelForeignKey, $parent_field_name)
    {
        $result = $this->getTableGateway()->select(array($mainModelForeignKey => $id, 'depth' => 1))->current();
        return $result[$parent_field_name];
    }
}