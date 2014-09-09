<?php
namespace Administration\Helper;


use Administration\Helper\DbGateway\CmsTableGateway;
use Administration\Helper\DbGateway\ModelTable;
use Administration\Helper\DbGateway\RelationTable;
use Administration\Helper\Manager\ModelManager;
use Administration\Helper\Manager\RelationManager;
use Zend\Db\Adapter\Adapter;

class RelationHandler
{
    protected $modelManager;
    protected $relatedModelManager;
    protected $adapter;
    protected $relationManager;
    protected $relatedModelTable;
    protected $relationTable = false;

    public function __construct(Array $relationDefinitionArray, Array $relatedModelDefinition, ModelManager $model, Adapter $adapter)
    {
        $this->modelManager = $model;
        $this->adapter      = $adapter;

        $this->relatedModelManager = new ModelManager($relatedModelDefinition);
        $this->relationManager     = new RelationManager($relationDefinitionArray, $this->modelManager->getPrefix(), $this->relatedModelManager->getPrefix());
        $this->relatedModelTable   = new ModelTable(
            new CmsTableGateway(
                $relationDefinitionArray['related_model'],
                $this->adapter
            ),
            array(),
            false
        );

        if ($this->relationManager->requiresTable()) {
            $gateway = new CmsTableGateway($this->relationManager->getTableName(), $this->adapter);
            $this->relationTable = new RelationTable($gateway, $this->relationManager->getTableColumnsDefinition(), $this->modelManager->getModelDbTableSync());
        }
    }

    public function getRelationManager()
    {
        return $this->relationManager;
    }

    public function getRelatedModelManager()
    {
        return $this->relatedModelManager;
    }

    public function getRelatedModelTable()
    {
        return $this->relatedModelTable;
    }

    public function getRelationTable()
    {
        return $this->relationTable;
    }
}