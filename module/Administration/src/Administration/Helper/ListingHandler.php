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
        $orderStatements           = array();

        if ($this->modelHandler->getTranslationManager()->requiresTable()) {
            $joinDefinitions[] = $this->modelHandler->getTranslationManager()->getTranslationTableJoinDefinition($this->languageHelper->getPrimaryLanguageId());
        }

        if ($this->modelHandler->getParentManager()->requiresTable()) {
            $joinDefinitions = array_merge($joinDefinitions, $this->modelHandler->getParentManager()->getParentTableJoinDefinition($parent));
            $orderStatements[] = 'breadcrumbs';
        }

        $returnFields = $this->modelHandler->getModelManager()->getTableSpecificListingFields(
            $this->modelHandler->getModelManager()->getListingFields()
        );

        $results = $this->modelHandler->getModelTable()->fetch(
            $returnFields,
            $joinDefinitions,
            $additionalWhereStatements,
            $orderStatements
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
}