<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Authentication;

use Authentication\Helper\Authentication\UserAuthAdapter;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Authentication\Helper\Authentication\SessionDBStorage;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Authentication\AuthenticationService;
use Zend\Session\Config\SessionConfig;


class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Session' => function ($sm) {
                        return new Container('Administration', $sm->get('SessionManager'));
                    },
                'SessionManager' => function(ServiceManager $sm) {
                        $config        = $sm->get('config');
                        $sessionConfig = new SessionConfig();
                        $sessionConfig->setOptions($config['session']);
                        $manager       = new SessionManager($sessionConfig);
                        $saveHandler   = $sm->get('SessionSaveHandler');
                        $saveHandler->open($sessionConfig->getOption('save_path'), 'administration');
                        $manager->setSaveHandler($saveHandler);
                        return $manager;
                    },
                'AuthStorage' => function(ServiceManager $sm) {
                        $manager     = $sm->get('SessionManager');
                        $authStorage = new SessionDBStorage('Administration', null, $manager);
                        return $authStorage;
                    },
                'AuthService' => function (ServiceManager $sm) {
                        $dbAdapter           = $sm->get('DbAdapter');
                        $dbTableAuthAdapter  = new UserAuthAdapter($dbAdapter, 'user','email','password');
                        $storage             = $sm->get('AuthStorage');
                        $authService         = new AuthenticationService($storage, $dbTableAuthAdapter);
                        return $authService;
                    },
            ),
        );
    }
}
