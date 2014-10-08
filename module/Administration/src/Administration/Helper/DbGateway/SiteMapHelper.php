<?php
namespace Administration\Helper\DbGateway;


use Administration\Helper\ModelHandler;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\Exception;


class SiteMapHelper
{
    protected $dbAdapter;
    protected $routesTableName = 'routes';
    protected $routesTableColumnDefinitions = array(
        'site_map_id' => 'int',
        'item_id' => 'int',
        'language_id' => 'int',
        'combined_slug' => 'varchar',
    );
    protected $routesTable;
    protected $siteMapTable;
    protected $languageHelper;

    public function __construct(Adapter $adapter, $syncDb = false)
    {
        $this->dbAdapter      = $adapter;
        $this->languageHelper = new SiteLanguageHelper($this->dbAdapter);
        $gateway              = new CmsTableGateway($this->routesTableName, $this->dbAdapter);
        $this->routesTable    = new RoutesTable($gateway,$this->routesTableColumnDefinitions, $syncDb);
        unset($gateway);
        $gateway              = new CmsTableGateway('site_map_translation', $this->dbAdapter);
        $this->siteMapTable   = new AbstractTable($gateway);
        unset($gateway);
    }

    public function saveRoutes($data, $model = null)
    {
        if (is_array($data) && array_key_exists('type', $data)) {
            if ($data['type'] == '0') {
                $this->saveRoutesForTypeStatic($data);
            } elseif ($data['type'] == '1') {
                $this->saveRoutesForTypeModel($data);
            }
        }
    }

    public function deleteRoutes($siteMapId)
    {
        $where = array('site_map_id' => $siteMapId);
        $this->routesTable->delete($where);
    }

    protected function saveRoutesForTypeStatic($data)
    {
        foreach ($this->languageHelper->getLanguages() as $languageId => $language) {
            foreach ($data['parent_id'] as $parentId) {
                $computedRoute = $this->getCombinedParentUri($parentId, $languageId, $language).$data['slug[' . $languageId . ']'];

                $route = $this->routesTable->getRouteByLanguageIdAndSiteMapId($languageId,$data['id'])->current();

                $routeData = array(
                    'site_map_id' => $data['id'],
                    'item_id' => null,
                    'language_id' => $languageId,
                    'combined_slug' => $computedRoute
                );
                if (!$route) {
                    $this->routesTable->insert($routeData);
                } elseif ($route && $route['combined_slug'] != $computedRoute) {
                    $this->routesTable->update($routeData);
                }
            }
        }
    }

    protected function saveRoutesForTypeModel($data)
    {
        $model = new ModelHandler($data['model'],$this->dbAdapter );
        if ($model->isInitialised()) {
            foreach ($this->languageHelper->getLanguages() as $languageId => $language) {
                foreach ($data['parent_id'] as $parentId) {

                    $results = $this->fetchItemsForParent($model, $languageId, 0);

                    foreach ($results as $result) {
                        $computedRoute = $this->getCombinedParentUri($parentId, $languageId, $language).$result[$model->getModelManager()->getMetaSlugFieldName()];
                        $route         = $this->routesTable->getRouteByLanguageIdAndSiteMapId($languageId, $data['id'], $result['id'])->current();
                        $routeData     = array(
                            'site_map_id'   => $data['id'],
                            'item_id'       => $result['id'],
                            'language_id'   => $languageId,
                            'combined_slug' => $computedRoute
                        );
                        if (!$route) {
                            $this->routesTable->insert($routeData);
                        } elseif ($route && $route['combined_slug'] != $computedRoute) {
                            $this->routesTable->update($routeData);
                        }
                    }
                }
            }
        }
    }

    protected function fetchItemsForParent(ModelHandler $model, $languageId, $parentId)
    {
        $joinDefinitions = array();
        if ($model->getTranslationManager()->requiresTable()) {
            $joinDefinitions[] = array(
                'table_name'          => $model->getTranslationManager()->getTableName(),
                'on_field_expression' => $model->getTranslationManager()->getTableName() . '.' . $model->getModelManager()->getPrefix() . 'id' . ' = ' . $model->getModelManager()->getTableName() . '.id',
                'return_fields'       => array($model->getModelManager()->getMetaSlugFieldName()),
                'where'               => array('language_id' => $languageId)
            );
        }

        if ($model->getParentManager()->requiresTable()) {
            $joinDefinitions[] = array(
                'table_name'          => $model->getParentManager()->getTableName(),
                'on_field_expression' => $model->getParentManager()->getTableName() . '.' . $model->getModelManager()->getPrefix() . 'id' . ' = ' . $model->getModelManager()->getTableName() . '.id',
                'return_fields'       => $model->getParentManager()->getTableSpecificListingFields(array($model->getParentManager()->getFieldName())),
                'where'               => array('parent_id' => $parentId)
            );
        }

        return $model->getModelTable()->fetchForListing(
            array('id'),
            $joinDefinitions,
            array('status' => '1'),
            false,
            $parentId
        );
    }


    protected function getCombinedParentUri($id, $languageId, $language)
    {
        if ($id != '0') {
            $tableName = $this->routesTableName;
            $result = $this->siteMapTable->getTableGateway()->select(function (Select $select) use ($id, $languageId, $tableName){
                $select->join($tableName, 'site_map_translation.site_map_id = ' . $tableName . '.site_map_id')
                       ->join('site_map', 'site_map_translation.site_map_id = site_map.id')
                       ->join('site_map_to_parent', 'site_map_to_parent.site_map_id = site_map.id')
                       ->where(array('site_map_translation.language_id' => $languageId, $tableName . '.site_map_id' => $id));
            })->current();
            return $result['combined_slug'] . '/';
        } else {
            if ($languageId == $this->languageHelper->getPrimaryLanguageId()) {
                return '/';
            } else {
                return '/' . $language['code'] . '/';
            }
        }
    }
}