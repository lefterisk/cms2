<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Administration;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap($e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractController', 'dispatch', function($e) {
            $controller      = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $serviceManager  = $e->getApplication()->getServiceManager();
            $config          = $serviceManager->get('config');
            //switching between Layouts based on current module, as per config module_template_map array
            if (isset($config['module_template_map'][$moduleNamespace]['layout'])) {
                $controller->layout($config['module_template_map'][$moduleNamespace]['layout']);
            }
            //switching between Template path stacks (directories) based on current module, as per config module_template_map array
            $templatePathResolver = $serviceManager->get('Zend\View\Resolver\TemplatePathStack');
            if (isset($config['module_template_map'][$moduleNamespace]['template_path_stack'])) {
                $templatePathResolver->setPaths(array($config['module_template_map'][$moduleNamespace]['template_path_stack']));
            }
        }, 100);
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
}
