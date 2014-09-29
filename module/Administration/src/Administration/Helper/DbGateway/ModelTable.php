<?php
namespace Administration\Helper\DbGateway;


use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select;

class ModelTable extends AbstractTable
{
    public function fetchForListing(Array $mainTableFields = array(), Array $joinsDefinitions = array(), Array $whereDefinitions = array(), $recursive = false, $treeLevel = 0)
    {
        $results = $this->tableGateway->select(function (Select $select)  use ($mainTableFields, $joinsDefinitions, $whereDefinitions) {

            $predicate = new Predicate();

            if (count($mainTableFields) > 0) {
                $mainTableFields = array_merge(array('id'), $mainTableFields);
                $select->columns($mainTableFields);
            }
            if (count($joinsDefinitions) > 0)
            {
                foreach ($joinsDefinitions as $join) {
                    $select->join($join['table_name'], $join['on_field_expression'], $join['return_fields']);
                    $whereDefinitions = array_merge($whereDefinitions, $join['where']);
                }
            }
            if (count($whereDefinitions) > 0) {
                foreach ($whereDefinitions  as $field => $value) {
                    $predicate->equalTo($field, $value);
                }
                $select->where($predicate);
            }
        });

        $resultArray = array();
        //Recursive call to get children items
        foreach ($results as $result) {
            $result['tree_level'] = $treeLevel;
            $resultArray[]        = $result;
            if ($recursive) {
                $whereDefinitions['parent_id'] = $result->id;
                $resultArray      = array_merge($resultArray, $this->fetchForListing($mainTableFields,$joinsDefinitions,$whereDefinitions, true, $treeLevel+1));
            }
        }

        return $resultArray;
    }

    public function fetchForRelationSelect(Array $fields, $where = array())
    {
        if (!empty($fields)) {
            $this->tableGateway->setColumns(array_merge($fields, array('id')));
        }


        if (is_array($where) && !empty($where) || $where instanceof Predicate) {
            $query = $this->tableGateway->select($where);
        } else {
            $query = $this->tableGateway->select();
        }
        return $query;
    }

    public function save($id, Array $fieldValues)
    {
        if (!empty($id)) {
            $this->getTableGateway()->update($fieldValues, array('id' => $id));
        } else {
            $this->getTableGateway()->insert($fieldValues);
        }
    }
}