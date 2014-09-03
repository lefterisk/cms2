<?php
namespace Administration\Helper\DbGateway;


use Zend\Db\Sql\Select;

class ModelTable extends AbstractTable
{
    public function fetchForListing($languageId, Array $mainTableFields = array(), Array $joinsDefinitions = array())
    {
        $result = $this->tableGateway->select(function (Select $select)  use ($languageId, $mainTableFields, $joinsDefinitions) {
            if (count($mainTableFields) > 0) {
                $select->columns($mainTableFields);
            }
            if (count($joinsDefinitions) > 0)
            {
                foreach ($joinsDefinitions as $join) {
                    $select->join($join['table_name'], $join['on_field_expression'], $join['return_fields']);
                }
            }
            $select
//               
                ->join('example_translation', 'example_translation.example_id = example.id', array(
                    'pipes_multi_var',
                ))
                ->where->equalTo('example_translation.language_id', $languageId);
        });
        return $result;
    }
}