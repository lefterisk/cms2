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

        //Switch between layouts/templates if route has been matched
        $eventManager->getSharedManager()->attach('Zend\Mvc\Controller\AbstractController', 'dispatch', function($e) {
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

        //Switch between layouts/templates if something went wrong (500,403,404)
        $eventManager->attach( MvcEvent::EVENT_DISPATCH_ERROR, function( MvcEvent $e ){

            $serviceManager  = $e->getApplication()->getServiceManager();
            $config          = $serviceManager->get('config');
            $path            = $e->getRequest()->getUri()->getPath();
            $viewModel       = $e->getResult();
            $layout          = $serviceManager->get( 'viewManager' )->getViewModel();

            //Default error layout & template are set as per module_template_map array "Application"
            $layout->setTemplate($config['module_template_map']['Application']['layout'] );
            $viewModel->setVariables( array( ) )->setTemplate($config['module_template_map']['Application']['exception_template']);

            //Loop through modules (module_template_map array) to override template & layout
            foreach ($config['module_template_map'] as $module => $module_conf) {
                if (
                    array_key_exists('url_regexp', $module_conf)
                    &&
                    !empty($module_conf['url_regexp'])
                    &&
                    preg_match($module_conf['url_regexp'], $path)
                ) {
                    $layout->setTemplate( $module_conf['layout'] );
                    switch ($e->getResponse()->getStatusCode()) {
                        case '500':
                            if (array_key_exists('exception_template', $module_conf) && !empty($module_conf['exception_template'])) {
                                $viewModel->setVariables( array( ) )->setTemplate( $module_conf['exception_template'] );
                            }
                            break;
                        case '404':
                        case '403':
                        default:
                            if (array_key_exists('not_found_template', $module_conf) && !empty($module_conf['not_found_template'])) {
                                $viewModel->setVariables( array( ) )->setTemplate( $module_conf['not_found_template'] );
                            }
                            break;
                    }
                }
            }
        });
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
                'DbAdapter' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return $dbAdapter;
                },
            ),
        );
    }
}
