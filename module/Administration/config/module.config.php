<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'administration' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/admin',
                    'defaults' => array(
                        'controller' => 'Administration\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'login' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/login[/action/:action]',
                            'constraints' => array(
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Administration\Controller\Login',
                                'action' => 'index'
                            )
                        )
                    ),
                    'model' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/model/:model[/parent/:parent][/item/:item][/action/:action]',
                            'constraints' => array(
                                'model'         => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'parent'        => '[0-9]*',
                                'item'          => '[0-9_-]*',
//                                'action'        => '(index|add|edit|save|delete|delete-multiple)',
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Administration\Controller\Model',
                                'action' => 'index'
                            )
                        )
                    )
                )
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Administration\Controller\Index' => 'Administration\Controller\IndexController',
            'Administration\Controller\Model' => 'Administration\Controller\ModelController',
            'Administration\Controller\Login' => 'Administration\Controller\LoginController',
        ),
    ),
    // Configuration so that we have different layouts for different modules - combined with Module.php onBootstrap
    'module_template_map' => array(
        'Administration' => array(
            'layout'              => 'layout/admin/layout',
            'template_path_stack' => __DIR__ . '/../view',
            'not_found_template'       => 'error/admin/404',
            'exception_template'       => 'error/admin/index',
            'url_regexp'               => '|^/admin.*$|'
        ),
        'Application'    => array(
            'layout'              => 'layout/app/layout',
            'template_path_stack' => __DIR__ . '/../../Application/view',
            'not_found_template'       => 'error/app/404',
            'exception_template'       => 'error/app/index',
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/admin/layout'     => __DIR__ . '/../view/layout/layout.phtml',
            'layout/app/layout'       => __DIR__ . '/../../Application/view/layout/layout.phtml',
            'error/admin/404'         => __DIR__ . '/../view/error/404.phtml',
            'error/app/404'           => __DIR__ . '/../../Application/view/error/404.phtml',
            'error/admin/index'       => __DIR__ . '/../view/error/index.phtml',
            'error/admin/model'       => __DIR__ . '/../view/administration/model/error.phtml',
            'error/app/index'         => __DIR__ . '/../../Application/view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
