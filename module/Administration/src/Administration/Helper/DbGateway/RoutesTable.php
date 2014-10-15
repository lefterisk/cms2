<?php
namespace Administration\Helper\DbGateway;


class RoutesTable extends AbstractTable
{
    public function insert($data)
    {
        $dataToSave = array(
            'site_map_id' => $data['site_map_id'],
            'site_map_parent_id' => $data['site_map_parent_id'],
            'item_id' => $data['item_id'],
            'item_parent_id' => $data['item_parent_id'],
            'route_type' => $data['route_type'],
            'model' => $data['model'],
            'language_id' => $data['language_id'],
            'combined_slug' => $data['combined_slug'],
        );
        $this->getTableGateway()->insert($dataToSave);
    }

    public function update($data)
    {
        $dataToSave = array(
            'combined_slug' => $data['combined_slug'],
        );
        $whereData = array(
            'site_map_id' => $data['site_map_id'],
            'site_map_parent_id' => $data['site_map_parent_id'],
            'item_id' => $data['item_id'],
            'item_parent_id' => $data['item_parent_id'],
            'route_type' => $data['route_type'],
            'model' => $data['model'],
            'language_id' => $data['language_id'],
        );
        $this->getTableGateway()->update($dataToSave,$whereData);
    }

    public function delete($data)
    {
        $this->getTableGateway()->delete($data);
    }

    public function getRoute($where)
    {
        return $this->getTableGateway()->select($where);
    }
}