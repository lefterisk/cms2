<?php
namespace Administration\Helper\DbGateway;


use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select;

class ModelTable extends AbstractTable
{
    public function fetch(Array $mainTableFields = array(), Array $joinsDefinitions = array(), Array $whereDefinitions = array(), Array $orderDefinitions = array())
    {
        $results = $this->tableGateway->select(function (Select $select)  use ($mainTableFields, $joinsDefinitions, $whereDefinitions,$orderDefinitions) {
            $predicate = new Predicate();

            if (count($mainTableFields) > 0) {
                $mainTableFields = array_merge(array('id'), $mainTableFields);
                $select->columns($mainTableFields);
            }
            if (count($joinsDefinitions) > 0) {
                foreach ($joinsDefinitions as $join) {
                    $select->join($join['table_name'], $join['on_field_expression'], $join['return_fields'], Select::JOIN_LEFT);
                    $whereDefinitions = array_merge($whereDefinitions, $join['where']);
                }
            }
            if (count($whereDefinitions) > 0) {
                foreach ($whereDefinitions as $field => $value) {
                    $predicate->equalTo($field, $value);
                }
                $select->where($predicate);
            }

            $select->group(array($select->getRawState('table').'.id'));
            $select->order($orderDefinitions);
//            var_dump($select->getSqlString());
        });
        return $results;
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