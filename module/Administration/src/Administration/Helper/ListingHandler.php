<?php
namespace Administration\Helper;

use Administration\Helper\DbGateway\SiteLanguageHelper;
use Zend\Db\Sql\Expression;

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
            $joinDefinitions[] = $this->getTranslationTableJoinDefinition($this->languageHelper->getPrimaryLanguageId());
        }

        if ($this->modelHandler->getParentManager()->requiresTable()) {
            $joinDefinitions = array_merge($joinDefinitions, $this->getParentTableJoinDefinition($parent));
        }

        $returnFields = $this->modelHandler->getModelManager()->getTableSpecificListingFields(
            $this->modelHandler->getModelManager()->getListingFields()
        );

        if ($this->modelHandler->getParentManager()->requiresTable()) {
            //$returnFields      = array_merge(array('breadcrumbs' => new Expression(" GROUP_CONCAT( crumbs.`". $this->modelHandler->getParentManager()->getFieldName() ."` SEPARATOR ',' ) ")),$returnFields);
            $orderStatements[] = 'breadcrumbs';
        }

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

    protected function getTranslationTableJoinDefinition($languageId)
    {
        return array(
            'table_name'          => $this->modelHandler->getTranslationManager()->getTableName(),
            'on_field_expression' => $this->modelHandler->getTranslationManager()->getTableName() . '.' . $this->modelHandler->getModelManager()->getPrefix() . 'id' . ' = ' . $this->modelHandler->getModelManager()->getTableName() . '.id',
            'return_fields'       => $this->modelHandler->getTranslationManager()->getTableSpecificListingFields($this->modelHandler->getModelManager()->getListingFields()),
            'where'               => array('language_id' => $languageId)
        );
    }

    protected function getParentTableJoinDefinition($parent = 0)
    {
        return array(
            array(
                'table_name'          => array('p' => $this->modelHandler->getParentManager()->getTableName()),
                'on_field_expression' => 'p.' . $this->modelHandler->getModelManager()->getPrefix() . 'id' . ' = ' . $this->modelHandler->getModelManager()->getTableName() . '.id',
                'return_fields'       => array_merge($this->modelHandler->getParentManager()->getTableSpecificListingFields(array('p.' . $this->modelHandler->getParentManager()->getFieldName())), array('depth')),
                'where'               => array('p.'.$this->modelHandler->getParentManager()->getFieldName() => $parent),
            ),
            array(
                'table_name'          => array('crumbs' => $this->modelHandler->getParentManager()->getTableName()),
                'on_field_expression' => 'crumbs.' . $this->modelHandler->getModelManager()->getPrefix() . 'id' . ' = ' . $this->modelHandler->getModelManager()->getTableName() . '.id',
                'return_fields'       => array('breadcrumbs' => new Expression(" GROUP_CONCAT( crumbs.`". $this->modelHandler->getParentManager()->getFieldName() ."` SEPARATOR ',' ) ")),
                'where'               => array()
            ),
        );
    }
}