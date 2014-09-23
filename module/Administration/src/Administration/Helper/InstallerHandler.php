<?php
namespace Administration\Helper;

use Administration\Helper\DbGateway\InstallerHelper;
use Zend\Db\Adapter\Adapter;

class InstallerHandler
{
    protected $dbAdapter;
    protected $dbHelper;
    protected $errors = array();
    protected $requiredModels = array(
        'admin_language' => array(
            array(
                'name' => 'English',
                'code' => 'en',
                'status' => '1',
                'is_primary' => '1'
            ),
        ),
        'site_language' => array(
            array(
                'name' => 'English',
                'code' => 'en',
                'status' => '1',
                'is_primary' => '1'
            )
        ),
        'user_group' => array(
            array(
                'name' => 'Administrator',
                'status' => '1',
                'view_models' => array(
                    'admin_language',
                    'example',
                    'site_language',
                    'tool_box',
                    'user',
                    'user_group'
                ),
                'add_models' => array(
                    'admin_language',
                    'example',
                    'site_language',
                    'tool_box',
                    'user',
                    'user_group'
                ),
                'edit_models' => array(
                    'admin_language',
                    'example',
                    'site_language',
                    'tool_box',
                    'user',
                    'user_group'
                ),
                'delete_models' => array(
                    'admin_language',
                    'example',
                    'site_language',
                    'tool_box',
                    'user',
                    'user_group'
                ),
            )
        ),
        'user' => array(
            array(
                'email' => 'administrator@gmail.com',
                'password' => 'password',
                'name' => 'Admin',
                'surname' => 'Administrator',
                'status' => '1',
                'user_group_id' => '1',
                'modified' => '2014-09-18 11:21:07',
            )
        ),
        'tool_box' => array(
            array(
                'name' => 'Admin Tools',
                'status' => '1',
                'models' => array(
                    'admin_language',
                    'site_language',
                    'tool_box',
                    'user',
                    'user_group'
                ),
                'user_group_id' => array(
                    '1'
                )
            )
        )
    );
    protected $nonModelRequiredTableDefinitions = array(
        'log' => 'CREATE TABLE `log` (`timestamp` varchar(30) DEFAULT NULL, `message` varchar(255) DEFAULT NULL, `priority_name` varchar(10) DEFAULT NULL, `user_id` int(11) DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
        'session' => 'CREATE TABLE `session` ( `id` char(32) NOT NULL DEFAULT "", `name` char(32) NOT NULL DEFAULT "", `modified` int(11) DEFAULT NULL, `lifetime` int(11) DEFAULT NULL, `data` text, PRIMARY KEY (`id`,`name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
    );
    protected $errorMsgArray = array(
        'ERROR_1' => 'Database does not exist',
    );

    public function __construct(Adapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
        $this->dbHelper  = new InstallerHelper($this->dbAdapter);

        if ($this->dbHelper->databaseExists()) {
            //Required Models
            foreach ($this->requiredModels as $model => $dataArray) {
                if (!$this->dbHelper->modelTableExists($model)) {
                    $modelHandler = $this->initialiseModel($model);
                    if ($modelHandler instanceof ModelHandler && $modelHandler && is_array($dataArray)) {
                        foreach ($dataArray as $data) {
                            $modelHandler->save($data);
                        }
                    }
                }
            }
            //Required tables not associated with models
            foreach ($this->nonModelRequiredTableDefinitions as $table => $definition) {
                if (!$this->dbHelper->modelTableExists($table)) {
                    $this->dbHelper->createTable($definition);
                }
            }
        } else {
            $this->errors[] = $this->errorMsgArray['ERROR_1'];
        }
    }

    public function initialiseModel($model)
    {
        $modelHandler = new ModelHandler($model, $this->dbAdapter);
        if (!$modelHandler->isInitialised()) {
            $this->errors = array_merge($this->errors, $modelHandler->getErrors());
            return false;
        } else {
            return $modelHandler;
        }

    }

    public function getErrors()
    {
        return $this->errors;
    }
}