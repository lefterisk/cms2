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
use Administration\Helper\DbGateway\SiteLanguageHelper;
use Zend\Db\TableGateway\TableGateway;
use Zend\Escaper\Escaper;
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
        $sessionContainer    = $e->getApplication()->getServiceManager()->get('Session');
        $serviceManager      = $e->getApplication()->getServiceManager();
        $config              = $serviceManager->get('config');
        $sharedEventManager  = $eventManager->getSharedManager();


        //Setting up the locale
        if (empty($sessionContainer->locale)) {
            $adminLanguageHelper      = $e->getApplication()->getServiceManager()->get('AdminLanguages');
            $sessionContainer->locale = $adminLanguageHelper->getDefaultAdminLocale();
        }
        $e->getApplication()->getServiceManager()->get('translator')->setLocale($sessionContainer->locale);

        //Switch between layouts/templates if route has been matched
        $sharedEventManager->attach('Zend\Mvc\Controller\AbstractController', 'dispatch', function(MvcEvent $e) use ($serviceManager,$config, $sharedEventManager) {
            $controller      = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));


            //if user not authenticated redirect to login
            $authService = $serviceManager->get('AuthService');
            if (!$authService->hasIdentity() && $moduleNamespace != 'Authentication') {
                $controller->plugin('redirect')->toRoute('administration/login');
            }

            //making identity available to layout
            if ($authService->hasIdentity() && $moduleNamespace != 'Authentication') {
                $controller->layout()->setVariable('identity', $authService->getIdentity());
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
            $controller->getEventManager()->attach('logAdd', function($e) {
                var_dump('add');
            },101);

            $controller->getEventManager()->attach('logEdit', function($e) {
                var_dump('edit');
            },102);

            $controller->getEventManager()->attach('logDeleteSingle', function($e) {
                var_dump('delete');
            },103);

            $controller->getEventManager()->attach('logDeleteMultiple', function($e) {
                var_dump('deleteMultiple');
            },104);

        }, 100);

        //Switch between layouts/templates if something went wrong (500,403,404)
        $eventManager->attach( MvcEvent::EVENT_DISPATCH_ERROR, function( MvcEvent $e ) use ($serviceManager,$config, $eventManager){

            $path            = $e->getRequest()->getUri()->getPath();
            $viewModel       = $e->getResult();
            $layout          = $serviceManager->get( 'viewManager' )->getViewModel();

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
            ),
        );
    }
}
