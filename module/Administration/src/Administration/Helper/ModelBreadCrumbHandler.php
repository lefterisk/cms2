<?php
namespace Administration\Helper;

use Administration\Helper\DbGateway\SiteLanguageHelper;

class ModelBreadCrumbHandler
{
    protected $modelHandler;
    protected $languageHelper;

    public function __construct(ModelHandler $modelHandler, SiteLanguageHelper $languageHelper)
    {
        $this->modelHandler   = $modelHandler;
        $this->languageHelper = $languageHelper;
    }

    public function getBreadCrumbLinksArray($currentItemId)
    {
        $linksArray = $this->getItemLinkRecursive($currentItemId);
        return array_reverse($linksArray);
    }

    protected function getItemLinkRecursive($id)
    {
        if ($id == 0) {
            return array();
        }
        $joinDefinitions           = array();
        $additionalWhereStatements = array();
        $recursive                 = false;
        $linksArray                = array();

        if ($this->modelHandler->getTranslationManager()->requiresTable()) {
            $joinDefinitions[] = $this->getTranslationTableJoinDefinition($this->languageHelper->getPrimaryLanguageId());
        }

        if ($this->modelHandler->getParentManager()->requiresTable()) {
            $joinDefinitions[] = $this->getParentTableJoinDefinition();
            //$additionalWhereStatements['parent_id'] = $parent;
        }

        $additionalWhereStatements['id'] = $id;

        $result = $this->modelHandler->getModelTable()->fetch(
            $this->modelHandler->getModelManager()->getTableSpecificListingFields(
                $this->modelHandler->getModelManager()->getListingFields()
            ),
            $joinDefinitions,
            $additionalWhereStatements,
            $recursive
        );

        $currentItem = reset($result);
        $linksArray[] = array('id' => $currentItem->id, 'parent_id' => $currentItem->parent_id, 'text' => $currentItem->{reset($this->modelHandler->getModelManager()->getListingFields())});
        if ($currentItem->parent_id != 0 ) {
            $linksArray = array_merge($linksArray, $this->getItemLinkRecursive($currentItem->parent_id));
        }

        return $linksArray;
    }

    protected function getTranslationTableJoinDefinition($languageId)
    {
        return array(
            'table_name'          => $this->modelHandler->getTranslationManager()->getTableName(),
            'on_field_expression' => $this->modelHandler->getTranslationManager()->getTableName() . '.' . $this->modelHandler->getModelManager()->getPrefix() . 'id' . ' = ' . $this->modelHandler->getModelManager()->getTableName() . '.id',
            'return_fields'       => $this->modelHandler->getTranslationManager()->getTableSpecificListingFields($this->modelHandler->getModelManager()->getListingFields()),
            'where'               => array('language_id' => $languageId)
        );
    }

    protected function getParentTableJoinDefinition()
    {
        return array(
            'table_name'          => $this->modelHandler->getParentManager()->getTableName(),
            'on_field_expression' => $this->modelHandler->getParentManager()->getTableName() . '.' . $this->modelHandler->getModelManager()->getPrefix() . 'id' . ' = ' . $this->modelHandler->getModelManager()->getTableName() . '.id',
            'return_fields'       => $this->modelHandler->getParentManager()->getTableSpecificListingFields(array($this->modelHandler->getParentManager()->getFieldName())),
            'where'               => array()
        );
    }
}