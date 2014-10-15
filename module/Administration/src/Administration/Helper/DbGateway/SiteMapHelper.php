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
        'site_map_id'           => 'int',
        'site_map_parent_id'    => 'int',
        'route_type'            => 'int',
        'model'                 => 'varchar',
        'item_id'               => 'int',
        'item_parent_id'        => 'int',
        'language_id'           => 'int',
        'combined_slug'         => 'varchar',
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

    public function saveRoutes($data)
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

                $route = $this->routesTable->getRoute(
                    array(
                        'language_id' => $languageId,
                        'site_map_id' => $data['id']
                    )
                )->current();

                $routeData = array(
                    'site_map_id' => $data['id'],
                    'site_map_parent_id' => $parentId,
                    'model' => null,
                    'item_id' => null,
                    'item_parent_id' => null,
                    'route_type' => $data['type'],
                    'language_id' => $languageId,
                    'combined_slug' => $computedRoute,
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
        $model = new ModelHandler($data['model'], $this->dbAdapter);
        if ($model->isInitialised()) {
            foreach ($this->languageHelper->getLanguages() as $languageId => $language) {
                foreach ($data['parent_id'] as $parentId) {
                    $parentModel = $this->parentModel($parentId);
                    if ($parentModel) {
                        $parentModelHandler = new ModelHandler($parentModel, $this->dbAdapter);
                        var_dump($model->getRelationHandlers()[$parentModelHandler->getModelManager()->getPrefix() . 'prefix']);
                    }

                    $results = $this->fetchItemsForParent($model, $languageId, 0);
                    $parentSiteMapRoute = $this->getCombinedParentUri($parentId, $languageId, $language['code']);
                    $this->recursiveModelRoute($results, 0, $parentSiteMapRoute, $parentId, $model->getModelManager()->getMetaSlugFieldName(), $languageId, $data);

                }
            }
        }
    }

    protected function recursiveModelRoute($results, $modelParentId, $parentSiteMapRoute, $siteMapParentId, $slugFieldName, $languageId, $data)
    {
        foreach ($results as $result) {
            if ($result['parent_id'] == $modelParentId) {
                $route = $this->routesTable->getRoute(
                    array(
                        'language_id' => $languageId,
                        'site_map_id' => $data['id'],
                        'site_map_parent_id' => $siteMapParentId,
                        'item_id' => $result['id'],
                        'item_parent_id' => $modelParentId,
                    )
                )->current();
                $computedRoute = $parentSiteMapRoute . $result[$slugFieldName];
                $routeData = array(
                    'site_map_id' => $data['id'],
                    'site_map_parent_id' => $siteMapParentId,
                    'model' => $data['model'],
                    'item_id' => $result['id'],
                    'item_parent_id' => $modelParentId,
                    'route_type' => $data['type'],
                    'language_id' => $languageId,
                    'combined_slug' => $computedRoute,
                );
                if (!$route) {
                    $this->routesTable->insert($routeData);
                } elseif ($route && $route['combined_slug'] != $computedRoute) {
                    $this->routesTable->update($routeData);
                }
                $this->recursiveModelRoute($results, $result['id'], $computedRoute . '/', $siteMapParentId, $slugFieldName, $languageId, $data);
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
                'where'               => array()
            );
        }

        return $model->getModelTable()->fetch(
            array('id'),
            $joinDefinitions,
            array('status' => '1','parent_id' => $parentId),
            true,
            $parentId
        );
    }

    protected function parentModel($id)
    {
        if ($id != '0') {
            $result = $this->routesTable->getTableGateway()->select(array('site_map_id' => $id))->current();
            if ($result['route_type'] == 1) {
                return $result['model'];
            }
            return false;
        }
        return false;
    }

    protected function getCombinedParentUri($id, $languageId, $languagePrefix)
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
                return '/' . $languagePrefix . '/';
            }
        }
    }
}