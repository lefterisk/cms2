<?php
namespace Administration\Helper\DbGateway;


class RoutesTable extends AbstractTable
{
    public function insert($data)
    {
        $dataToSave = array(
            'site_map_id' => $data['site_map_id'],
            'item_id' => $data['item_id'],
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
            'item_id' => $data['item_id'],
            'language_id' => $data['language_id'],
        );
        $this->getTableGateway()->update($dataToSave,$whereData);
    }

    public function delete($data)
    {
        $this->getTableGateway()->delete($data);
    }

    public function getRouteByLanguageIdAndSiteMapId($languageId, $siteMapId, $item = false)
    {
        $where = array('language_id' => $languageId, 'site_map_id' => $siteMapId);
        if ($item) {
            $where['item_id'] = $item;
        }
        return $this->getTableGateway()->select($where);
    }
}