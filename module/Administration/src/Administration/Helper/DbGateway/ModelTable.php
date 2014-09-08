<?php
namespace Administration\Helper\DbGateway;


use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select;

class ModelTable extends AbstractTable
{
    public function fetchForListing(Array $mainTableFields = array(), Array $joinsDefinitions = array(), Array $whereDefinitions = array())
    {
        $result = $this->tableGateway->select(function (Select $select)  use ($mainTableFields, $joinsDefinitions, $whereDefinitions) {

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
        return $result;
    }

    public function fetchForRelationSelect(Array $fields, $where = array())
    {
        if (!empty($fields)) {
            $this->tableGateway->setColumns(array_merge($fields, array('id')));
        }

        $query = $this->tableGateway->select();

        if (is_array($where) && !empty($where) || $where instanceof Predicate) {
            $query->where($where);
        }
        return $query;
    }
}