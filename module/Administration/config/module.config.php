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
                                'controller' => 'Authentication\Controller\Index',
                                'action' => 'index'
                            )
                        )
                    ),
                    'logout' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/logout',
                            'defaults' => array(
                                'controller' => 'Authentication\Controller\Index',
                                'action' => 'logout'
                            )
                        )
                    ),
                    'model' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/model/:model[/parent/:parent][/item/:item][/action/:action][/field/:field/value/:value]',
                            'constraints' => array(
                                'model'         => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'parent'        => '[0-9]*',
                                'item'          => '[0-9_-]*',
                                //'action'        => '(index|add|edit|save|delete|delete-multiple)',
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'field'         => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'value'         => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Administration\Controller\Model',
                                'action' => 'index'
                            )
                        )
                    ),
                    'language' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/language/:language',
                            'constraints' => array(
                                'language' => '[0-9_-]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Administration\Controller\Language',
                                'action' => 'index'
                            )
                        )
                    ),
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
        'locale' => 'en',
        'translation_file_patterns' => array(
            array(
                'type'     => 'phpArray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.php',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Administration\Controller\Index'    => 'Administration\Controller\IndexController',
            'Administration\Controller\Model'    => 'Administration\Controller\ModelController',
            'Administration\Controller\Language' => 'Administration\Controller\LanguageController',
        ),
    ),
    // If set, the system auto-installs specific required models as per InstallerHelper
    'auto_install' => true,
    // Configuration so that we have different layouts for different modules - combined with Module.php onBootstrap
    'module_template_map' => array(
        'Administration' => array(
            'layout'              => 'layout/admin/layout',
            'template_path_stack' => __DIR__ . '/../view',
            'not_found_template'  => 'error/admin/404',
            'exception_template'  => 'error/admin/index',
            'url_regexp'          => '|^/admin.*$|'
        ),
        'Authentication' => array(
            'layout'              => 'layout/login/layout',
            'template_path_stack' => __DIR__ . '/../../Authentication/view',
            'not_found_template'  => 'error/login/404',
            'exception_template'  => 'error/login/index',
            'url_regexp'          => '|^/admin/login.*$|'
        ),
        'Application'    => array(
            'layout'              => 'layout/app/layout',
            'template_path_stack' => __DIR__ . '/../../Application/view',
            'not_found_template'  => 'error/app/404',
            'exception_template'  => 'error/app/index',
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
            'layout/login/layout'     => __DIR__ . '/../../Authentication/view/layout/layout.phtml',
            'error/admin/404'         => __DIR__ . '/../view/error/404.phtml',
            'error/app/404'           => __DIR__ . '/../../Application/view/error/404.phtml',
            'error/login/404'         => __DIR__ . '/../../Authentication/view/error/404.phtml',
            'error/admin/index'       => __DIR__ . '/../view/error/index.phtml',
            'error/admin/model'       => __DIR__ . '/../view/administration/model/error.phtml',
            'error/app/index'         => __DIR__ . '/../../Application/view/error/index.phtml',
            'error/login/index'       => __DIR__ . '/../../Authentication/view/error/index.phtml',
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
    'session' => array(
        'remember_me_seconds' => 2419200,
        'use_cookies' => true,
        'cookie_httponly' => true,
    ),
);
