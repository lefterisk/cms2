<?php
namespace Administration\Helper;


use Administration\Helper\DbGateway\CmsTableGateway;
use Administration\Helper\DbGateway\CustomSelectionTable;
use Administration\Helper\Manager\CustomSelectionManager;
use Administration\Helper\Manager\ModelManager;
use Zend\Db\Adapter\Adapter;

class CustomSelectionHandler
{
    protected $modelManager;
    protected $adapter;
    protected $customSelectionManager;
    protected $customSelectionTable = false;

    public function __construct(Array $definitionArray, ModelManager $model, Adapter $adapter)
    {
        $this->modelManager = $model;
        $this->adapter      = $adapter;

        $this->customSelectionManager = new CustomSelectionManager($definitionArray, $this->modelManager->getPrefix());

        if ($this->customSelectionManager->requiresTable()) {
            $this->customSelectionTable = new CustomSelectionTable(
                new CmsTableGateway($this->customSelectionManager->getTableName(), $this->adapter),
                $this->customSelectionManager->getTableColumnsDefinition(),
                $this->modelManager->getModelDbTableSync()
            );
        }
    }

    public function getCustomSelectionManager()
    {
        return $this->customSelectionManager;
    }

    public function getCustomSelectionTable()
    {
        return $this->customSelectionTable;
    }
}