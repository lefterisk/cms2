<?php
namespace Administration\Helper\DbGateway;


use Administration\Helper\ModelHandler;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\Exception;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;


class PermissionHelper
{
    protected $dbAdapter;
    protected $sql;
    protected $acl;
    protected $permittedActionsArray = array('view','add','edit','delete');

    public function __construct(Adapter $adapter)
    {
        $this->dbAdapter = $adapter;
        $this->sql       = new Sql($this->dbAdapter);
        $this->acl       = new Acl();
    }

    public function getAclForGroupAndModel($groupId, $groupName, $model)
    {
        $this->acl->addRole(new Role($groupName));
        $permittedActions = array();
        foreach ($this->permittedActionsArray as $action) {
            if ($this->isGroupPermittedActionForModel($groupId, $action, $model)) {
                $permittedActions[] = $action;
            }
        }
        $this->acl->allow($groupName, null, $permittedActions);

        return $this->acl;
    }

    protected function isGroupPermittedActionForModel($groupId, $action, $model)
    {
        if (in_array($action, $this->permittedActionsArray)) {
            $statement    = $this->sql->select('user_group_to_' . $action . '_models')->where(array($action . '_models' => $model,'user_group_id' => $groupId));
            $selectString = $this->sql->getSqlStringForSqlObject($statement);
            $results      = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
            if (count($results) > 0) {
                return true;
            }
        }
        return false;
    }

    public function getPermittedToolBoxes($groupId)
    {
        $statement    = $this->sql->select('tool_box_to_user_group')->where(array('user_group_id' => $groupId));
        $selectString = $this->sql->getSqlStringForSqlObject($statement);
        $results      = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $permittedToolBoxes = array();
        if (count($results) > 0) {
            $model = new ModelHandler('tool_box', $this->dbAdapter);
            foreach ($results as $tool_box_id) {
                try {
                    $permittedToolBoxes[] = $model->getItemById($tool_box_id['tool_box_id']);
                } catch (\Exception $ex) {
                    //@todo Handle this scenario
                }
            }
        }
        return $permittedToolBoxes;
    }
}