<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Administration;

use Administration\Helper\DbGateway\AdminLanguageHelper;
use Administration\Helper\DbGateway\PermissionHelper;
use Administration\Helper\DbGateway\SiteLanguageHelper;
use Administration\Helper\InstallerHandler;
use Zend\Db\TableGateway\TableGateway;
use Zend\Escaper\Escaper;
use Zend\Log\Logger;
use Zend\Log\Writer\Db;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;


class Module
{
    public function onBootstrap($e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $serviceManager      = $e->getApplication()->getServiceManager();
        $config              = $serviceManager->get('config');
        $sharedEventManager  = $eventManager->getSharedManager();

        //Install if auto-install config setting is set to true
        if (array_key_exists('auto_install', $config) && $config['auto_install']) {
            $installer = $e->getApplication()->getServiceManager()->get('InstallerHandler');
            if (count($installer->getErrors()) > 0) {
                var_dump($installer->getErrors());
                die();
            }
        }

        $sessionContainer    = $e->getApplication()->getServiceManager()->get('Session');

        //Setting up the locale
        if (empty($sessionContainer->locale)) {
            $adminLanguageHelper      = $e->getApplication()->getServiceManager()->get('AdminLanguages');
            $sessionContainer->locale = $adminLanguageHelper->getDefaultAdminLocale();
        }
        $e->getApplication()->getServiceManager()->get('translator')->setLocale($sessionContainer->locale);

        //Switch between layouts/templates if route has been matched
        $sharedEventManager->attach('Zend\Mvc\Controller\AbstractController', 'dispatch', function(MvcEvent $e) use ($serviceManager, $config, $sharedEventManager) {
            $controller      = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));

            //if user not authenticated redirect to login
            $authService = $serviceManager->get('AuthService');
            //pass identity to controller
            $controller->identity = $authService->getIdentity();
            if (!$authService->hasIdentity() && $moduleNamespace != 'Authentication') {
                return $controller->plugin('redirect')->toRoute('administration/login');
            }

            //ACL setup for Group & model
            $permissionHelper = $serviceManager->get('PermissionHelper');
            //passing the Acl to the controller
            $controller->acl  = $permissionHelper->getAclForGroupAndModel($controller->identity['user_group_id'],$controller->identity['user_group_name'],$e->getRouteMatch()->getParam('model'));


            //making identity available to layout
            if ($authService->hasIdentity() && $moduleNamespace != 'Authentication') {
                $controller->layout()->setVariable('identity', $controller->identity);
            }

            //making the escaper available in all views
            $controller->layout()->setVariable('escaper', new Escaper('utf-8'));

            //switching between Layouts based on current module, as per config module_template_map array
            if (isset($config['module_template_map'][$moduleNamespace]['layout'])) {
                $controller->layout($config['module_template_map'][$moduleNamespace]['layout']);
            }
            //switching between Template path stacks (directories) based on current module, as per config module_template_map array
            $templatePathResolver = $serviceManager->get('Zend\View\Resolver\TemplatePathStack');
            if (isset($config['module_template_map'][$moduleNamespace]['template_path_stack'])) {
                $templatePathResolver->setPaths(array($config['module_template_map'][$moduleNamespace]['template_path_stack']));
            }

            //Add action listeners
            //Logging
            $controller->getEventManager()->attach('logAction', function($e) use ($serviceManager,$authService){
                $params   = $e->getParams();
                $logger   = $serviceManager->get('LogHelper');
                $userData = array();

                if ($authService->hasIdentity()) {
                    $user     = $authService->getIdentity();
                    $userData = array('user_id' => $user['id']);
                }

                if (array_key_exists('type', $params) && method_exists($logger,$params['type'])) {
                    $logger->{$params['type']}($params['message'], $userData);
                } else {
                    $logger->info($params['message'], $userData);
                }
            }, 101);
        }, 100);

        //Switch between layouts/templates if something went wrong (500,403,404)
        $eventManager->attach( MvcEvent::EVENT_DISPATCH_ERROR, function( MvcEvent $e ) use ($serviceManager,$config, $eventManager){

            $path      = $e->getRequest()->getUri()->getPath();
            $viewModel = $e->getResult();
            $layout    = $serviceManager->get( 'viewManager' )->getViewModel();

            //Default error layout & template are set as per module_template_map array "Application"
            $layout->setTemplate($config['module_template_map']['Application']['layout'] );
            $layout->setVariable('escaper', new Escaper('utf-8'));
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
                    $authService = $serviceManager->get('AuthService');
                    //if not logged in redirect to login
                    if (!$authService->hasIdentity() && $module == 'Administration') {
                        $url      = $e->getRouter()->assemble(array('action' => 'index'), array('name' => 'administration/login'));
                        $response = $e->getResponse();
                        $response->getHeaders()->addHeaderLine('Location', $url);
                        $response->setStatusCode(302);
                        $response->sendHeaders();
                        exit;
                    } else {
                        if ($authService->hasIdentity()) {
                            $layout->setVariable('identity', $authService->getIdentity());
                        }
                        $layout->setTemplate( $module_conf['layout'] );
                    }
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
                'SiteLanguages' => function ($sm) {
                    $dbAdapter = $sm->get('DbAdapter');
                    return new SiteLanguageHelper($dbAdapter);
                },
                'AdminLanguages' => function ($sm) {
                    $dbAdapter = $sm->get('DbAdapter');
                    return new AdminLanguageHelper($dbAdapter);
                },
                'SessionSaveHandler' => function ($sm) {
                    $dbAdapter     = $sm->get('DbAdapter');
                    $tableGateway  = new TableGateway('Session', $dbAdapter);
                    $saveHandler   = new DbTableGateway($tableGateway, new DbTableGatewayOptions());
                    return $saveHandler;
                },
                'LogHelper' => function ($sm) {
                    $dbAdapter = $sm->get('DbAdapter');
                    $mapping   = array(
                        'timestamp'    => 'timestamp',
                        'priorityName' => 'priority_name',
                        'message'      => 'message',
                        'extra'        => array(
                            'user_id'      => 'user_id',
                        )
                    );
                    $writer = new Db($dbAdapter, 'log', $mapping);
                    $logger = new Logger();
                    $logger->addWriter($writer);
                    return $logger;
                },
                'PermissionHelper' => function ($sm) {
                    $dbAdapter = $sm->get('DbAdapter');
                    return new PermissionHelper($dbAdapter);
                },
                'InstallerHandler' => function ($sm) {
                    $dbAdapter = $sm->get('DbAdapter');
                    return new InstallerHandler($dbAdapter);
                }
            ),
        );
    }
}
