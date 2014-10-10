<?php
namespace Administration\Helper;

use Administration\Helper\DbGateway\SiteLanguageHelper;

class ListingHandler
{
    protected $modelHandler;
    protected $languageHelper;

    public function __construct(ModelHandler $modelHandler, SiteLanguageHelper $languageHelper)
    {
        $this->modelHandler   = $modelHandler;
        $this->languageHelper = $languageHelper;
    }

    public function getListing($parent = 0)
    {
        $joinDefinitions           = array();
        $additionalWhereStatements = array();
        $recursive                 = false;

        if ($this->modelHandler->getTranslationManager()->requiresTable()) {
            $joinDefinitions[] = $this->getTranslationTableJoinDefinition($this->languageHelper->getPrimaryLanguageId());
        }

        if ($this->modelHandler->getParentManager()->requiresTable()) {
            $joinDefinitions[] = $this->getParentTableJoinDefinition();
            $additionalWhereStatements['parent_id'] = $parent;
            $recursive = false;
        }

        $results = $this->modelHandler->getModelTable()->fetch(
            $this->modelHandler->getModelManager()->getTableSpecificListingFields(
                $this->modelHandler->getModelManager()->getListingFields()
            ),
            $joinDefinitions,
            $additionalWhereStatements,
            $recursive
        );
        return $results;
    }

    public function getListingFieldsDefinitions()
    {
        $fieldDefinitions = array();
        foreach ($this->modelHandler->getModelManager()->getListingFields() as $field) {
            if (in_array($field, $this->modelHandler->getModelManager()->getBooleans())) {
                $fieldDefinitions[$field] = 'boolean';
            } elseif (in_array($field, $this->modelHandler->getModelManager()->getDates())) {
                $fieldDefinitions[$field] = 'date';
            } else {
                $fieldDefinitions[$field] = 'varchar';
            }
        }
        return $fieldDefinitions;
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