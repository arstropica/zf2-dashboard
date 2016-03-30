<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
$env = getenv('APPLICATION_ENV') ?  : 'production';
return array (
		'controllers' => array (
				'invokables' => array (
						'Application\Controller\Index' => 'Application\Controller\IndexController',
						'Application\Controller\Navigation' => 'Application\Controller\NavigationController' 
				) 
		),
		'router' => array (
				'routes' => array (
						'home' => array (
								'type' => 'Zend\Mvc\Router\Http\Literal',
								'options' => array (
										'route' => '/',
										'defaults' => array (
												'controller' => 'Application\Controller\Index',
												'action' => 'index' 
										) 
								) 
						),
						'dashboard' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/dashboard',
										'defaults' => array (
												'controller' => 'Application\Controller\Index',
												'action' => 'dashboard' 
										) 
								) 
						),
						'navigation' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/navigation',
										'defaults' => array (
												'controller' => 'Application\Controller\Navigation',
												'action' => 'index' 
										) 
								) 
						),
						'error' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/error',
										'defaults' => array (
												'controller' => 'Application\Controller\Index',
												'action' => 'error' 
										) 
								),
								'may_terminate' => false,
								'child_routes' => array (
										'403' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/403',
														'defaults' => array (
																'controller' => 'Application\Controller\Error',
																'action' => 'error403' 
														) 
												) 
										) 
								) 
						) 
				) 
		),
		'controller_plugins' => array (
				'factories' => array (
						'getJsonErrorResponse' => 'Application\Controller\Plugin\Factory\JsonErrorResponseFactory',
						'getErrorResponse' => 'Application\Controller\Plugin\Factory\ErrorResponseFactory',
						'SessionHistory' => 'Application\Controller\Plugin\Factory\SessionHistoryFactory' 
				),
				'invokables' => array () 
		),
		'service_manager' => array (
				'initializers' => array (
						'Application\Initializer\ElasticaInitializer',
						'Application\Initializer\SearchManagerInitializer' 
				),
				'invokables' => array (
						'Application\Form\PagerForm' => 'Application\Form\PagerForm',
						'Application\Controller\Plugin\JSONErrorResponse' => 'Application\Controller\Plugin\JSONErrorResponse',
						'Application\Controller\Plugin\ErrorResponse' => 'Application\Controller\Plugin\ErrorResponse' 
				),
				'abstract_factories' => array (
						'Zend\Navigation\Service\NavigationAbstractServiceFactory',
						'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
						'Zend\Log\LoggerAbstractServiceFactory' 
				),
				'factories' => array (
						'translator' => 'Zend\Mvc\Service\TranslatorServiceFactory',
						'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
						'Application\Options\ModuleOptions' => 'Application\Options\Factory\ModuleOptionsFactory',
						'Application\Service\Lead' => 'Application\Service\Factory\LeadServiceFactory',
						'Application\Service\Account' => 'Application\Service\Factory\AccountServiceFactory',
						'Application\Service\Event' => 'Application\Service\Factory\EventServiceFactory',
						'Application\Service\Factory\SessionHistoryServiceFactory' => 'Application\Service\Factory\SessionHistoryServiceFactory' 
				) 
		),
		'translator' => array (
				'locale' => 'en_US',
				'translation_file_patterns' => array (
						array (
								'type' => 'gettext',
								'base_dir' => __DIR__ . '/../language',
								'pattern' => '%s.mo' 
						) 
				) 
		),
		'view_manager' => array (
				'display_not_found_reason' => true,
				'display_exceptions' => ($env == 'production') ? false : true,
				'doctype' => 'HTML5',
				'not_found_template' => 'error/404',
				'exception_template' => 'error/index',
				'template_map' => array (
						'paginator-slide' => __DIR__ . '/../view/layout/paginator.phtml',
						'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
						'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
						'error/403' => __DIR__ . '/../view/error/403.phtml',
						'error/404' => __DIR__ . '/../view/error/404.phtml',
						'error/index' => __DIR__ . '/../view/error/index.phtml' 
				),
				'template_path_stack' => array (
						__DIR__ . '/../view' 
				) 
		),
		'view_helpers' => array (
				'invokables' => array (
						'form' => 'Application\Form\View\Helper\Form',
						'formRow' => 'Application\Form\View\Helper\FormRow',
						'formDateRange' => 'Application\Form\View\Helper\FormDateRange',
						'formSlider' => 'Application\Form\View\Helper\FormSlider',
						'fieldCollection' => 'Application\Form\View\Helper\FieldCollection',
						'routeLabel' => 'Application\View\Helper\RouteLabel',
						'tableCollapse' => 'Application\View\Helper\TableCollapse',
						'getRepo' => 'Application\View\Helper\EntityRepo',
						'paginatorPosition' => 'Application\View\Helper\PaginatorPosition' 
				),
				'factories' => array (
						'formElement' => 'Application\Form\View\Helper\Factory\FormElementFactory' 
				) 
		),
		'view_helper_config' => array (
				'flashmessenger' => array (
						'message_open_format' => '<div%s><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>',
						'message_close_string' => '</li></ul></div>',
						'message_separator_string' => '</li><li>' 
				) 
		),
		// Placeholder for console routes
		'console' => array (
				'router' => array (
						'routes' => array () 
				) 
		),
		'form_options' => array (
				'ignoredViewHelpers' => array (
						'file',
						'checkbox',
						'radio',
						'submit',
						'multi_checkbox',
						'button',
						'reset' 
				) 
		),
		'app_options' => array (
				'exceptions_from_errors' => true,
				'recover_from_fatal' => false,
				'fatal_errors_callback' => function ($s_msg, $s_file, $s_line) {
					return false;
				},
				'redirect_url' => '/error',
				
				'php_settings' => array (
						'error_reporting' => E_ALL,
						'display_errors' => 'On',
						'display_startup_errors' => 'Off' 
				) 
		),
		'log' => array (
				'file' => 'logs/' . date('Y-m') . '.log' 
		),
		'navigation' => array (
				'default' => array (
						array (
								'label' => 'Home',
								'route' => 'home',
								'icon' => 'glyphicon glyphicon-dashboard',
								'pages' => array (
										array (
												'label' => 'Dashboard',
												'route' => 'dashboard',
												'action' => 'dashboard' 
										),
										array (
												'label' => 'Event Log',
												'route' => 'event',
												'action' => 'list',
												'resource' => 'navigation',
												'privilege' => 'display' 
										) 
								) 
						),
						array (
								'label' => 'Leads',
								'route' => 'lead',
								'resource' => 'navigation',
								'privilege' => 'display',
								'icon' => 'glyphicon glyphicon-stats',
								'pages' => array (
										array (
												'label' => 'Display Leads',
												'route' => 'lead',
												'action' => 'list' 
										),
										array (
												'label' => 'Search Leads',
												'route' => 'lead/search',
												'action' => 'search' 
										),
										array (
												'label' => 'Import Leads',
												'route' => 'import',
												'action' => 'import' 
										),
										array (
												'label' => 'Add Lead',
												'route' => 'lead/add',
												'action' => 'add' 
										),
										array (
												'label' => 'Lead Attributes',
												'route' => 'attribute',
												'action' => 'list' 
										),
										array (
												'label' => 'Manage Sources',
												'route' => 'source',
												'action' => 'list' 
										),
										array (
												'label' => 'Search Result',
												'route' => 'lead/search/result',
												'action' => 'result',
												'visible' => false 
										),
										array (
												'label' => 'View Lead',
												'route' => 'lead/view',
												'action' => 'view',
												'visible' => false 
										),
										array (
												'label' => 'Assign Lead',
												'route' => 'lead/edit',
												'action' => 'edit',
												'visible' => false 
										),
										array (
												'label' => 'Delete Lead',
												'route' => 'lead/delete',
												'action' => 'delete',
												'visible' => false 
										),
										array (
												'label' => 'Submit Lead',
												'route' => 'lead/submit',
												'action' => 'submit',
												'visible' => false 
										) 
								) 
						),
						array (
								'label' => 'Accounts',
								'route' => 'account',
								'resource' => 'navigation',
								'privilege' => 'display',
								'icon' => 'glyphicon glyphicon-briefcase',
								'pages' => array (
										array (
												'label' => 'Display Accounts',
												'route' => 'account',
												'action' => 'list' 
										),
										array (
												'label' => 'Add Account',
												'route' => 'account/add',
												'action' => 'add' 
										),
										array (
												'label' => 'View Account',
												'route' => 'account/view',
												'action' => 'view',
												'visible' => false 
										),
										array (
												'label' => 'Edit Account',
												'route' => 'account/edit',
												'action' => 'edit',
												'visible' => false 
										),
										array (
												'label' => 'Archive Account',
												'route' => 'account/delete',
												'action' => 'delete',
												'visible' => false 
										) 
								) 
						),
						array (
								'label' => 'Services',
								'route' => 'services',
								'resource' => 'navigation',
								'privilege' => 'display',
								'icon' => 'glyphicon glyphicon-star',
								'pages' => array (
										array (
												'label' => 'TenStreet',
												'route' => 'services/tenstreet',
												'action' => 'list' 
										),
										array (
												'label' => 'TenStreet',
												'route' => 'services/tenstreet/list',
												'action' => 'list',
												'visible' => false 
										),
										array (
												'label' => 'Email',
												'route' => 'services/email',
												'action' => 'list' 
										),
										array (
												'label' => 'Email',
												'route' => 'services/email/list',
												'action' => 'list',
												'visible' => false 
										),
										array (
												'label' => 'Options',
												'route' => 'api/list',
												'action' => 'list' 
										) 
								) 
						),
						array (
								'label' => 'Reports',
								'route' => 'report',
								'resource' => 'navigation',
								'privilege' => 'display',
								'icon' => 'glyphicon glyphicon-search',
								'pages' => array (
										array (
												'label' => 'Display Reports',
												'route' => 'report',
												'action' => 'list' 
										),
										array (
												'label' => 'Index Manager',
												'route' => 'report/index',
												'action' => 'list' 
										),
										array (
												'label' => 'Display Reports',
												'route' => 'report/list',
												'action' => 'list',
												'visible' => false 
										),
										array (
												'label' => 'Create Report',
												'route' => 'report/add',
												'action' => 'add' 
										),
										array (
												'label' => 'View Report',
												'route' => 'report/view',
												'action' => 'view',
												'visible' => false 
										),
										array (
												'label' => 'Edit Report',
												'route' => 'report/edit',
												'action' => 'edit',
												'visible' => false 
										),
										array (
												'label' => 'Archive Report',
												'route' => 'report/delete',
												'action' => 'delete',
												'visible' => false 
										) 
								) 
						),
						array (
								'label' => 'Profile',
								'route' => 'zfcuser',
								'icon' => 'glyphicon glyphicon-user',
								'pages' => array (
										array (
												'label' => 'Your Profile',
												'route' => 'zfcuser',
												'action' => 'index',
												'resource' => 'navigation',
												'privilege' => 'display' 
										),
										array (
												'label' => 'Register',
												'route' => 'zfcuser/register',
												'action' => 'register',
												'visible' => false,
												'resource' => 'navigation',
												'privilege' => 'display' 
										),
										array (
												'label' => 'Login',
												'route' => 'zfcuser/login',
												'action' => 'login',
												'resource' => 'navigation',
												'privilege' => 'login' 
										),
										array (
												'label' => 'Change Email',
												'route' => 'zfcuser/changeemail',
												'action' => 'changeemail',
												'resource' => 'navigation',
												'privilege' => 'display' 
										),
										array (
												'label' => 'Change Password',
												'route' => 'zfcuser/changepassword',
												'action' => 'changepassword',
												'resource' => 'navigation',
												'privilege' => 'display' 
										),
										array (
												'label' => 'Logout',
												'route' => 'zfcuser/logout',
												'action' => 'logout',
												'resource' => 'navigation',
												'privilege' => 'logout' 
										) 
								) 
						) 
				),
				'special' => array (
						array (
								'label' => 'Home',
								'route' => 'home',
								'pages' => array (
										array (
												'label' => 'Dashboard',
												'route' => 'dashboard',
												'action' => 'dashboard' 
										) 
								) 
						),
						array (
								'label' => 'Leads',
								'route' => 'lead',
								'pages' => array (
										array (
												'label' => 'Display Leads',
												'route' => 'lead',
												'action' => 'list' 
										),
										array (
												'label' => 'Display Leads',
												'route' => 'lead/list',
												'action' => 'list' 
										),
										array (
												'label' => 'Search Leads',
												'route' => 'lead/search',
												'action' => 'search',
												'visible' => true,
												'dynamic' => true,
												'pages' => array (
														array (
																'label' => 'Search Result',
																'route' => 'lead/search/result',
																'action' => 'result',
																'visible' => true 
														) 
												) 
										),
										array (
												'label' => 'Import Leads',
												'route' => 'import',
												'action' => 'import' 
										),
										array (
												'label' => 'Add Lead',
												'route' => 'lead/add',
												'action' => 'add' 
										),
										array (
												'label' => 'View Lead',
												'route' => 'lead/view',
												'action' => 'view',
												'visible' => true 
										),
										array (
												'label' => 'Assign Lead',
												'route' => 'lead/edit',
												'action' => 'edit',
												'visible' => true 
										),
										array (
												'label' => 'Delete Lead',
												'route' => 'lead/delete',
												'action' => 'delete',
												'visible' => true 
										),
										array (
												'label' => 'Submit Lead',
												'route' => 'lead/submit',
												'action' => 'submit',
												'visible' => true 
										),
										array (
												'label' => 'Attributes',
												'route' => 'attribute',
												'pages' => array (
														array (
																'label' => 'Lead Attributes',
																'route' => 'attribute/list',
																'action' => 'list' 
														),
														array (
																'label' => 'Edit Attribute',
																'route' => 'attribute/edit',
																'action' => 'edit' 
														),
														array (
																'label' => 'Add Attribute',
																'route' => 'attribute/add',
																'action' => 'add' 
														),
														array (
																'label' => 'Delete Attribute',
																'route' => 'attribute/delete',
																'action' => 'delete' 
														),
														array (
																'label' => 'Merge Attribute',
																'route' => 'attribute/merge',
																'action' => 'merge' 
														), 
														array (
																'label' => 'Lead Geo-Update Tool',
																'route' => 'attribute/geo',
																'action' => 'geo' 
														) 
												) 
										), 
										array (
												'label' => 'Sources',
												'route' => 'source',
												'pages' => array (
														array (
																'label' => 'Manage Sources',
																'route' => 'source/list',
																'action' => 'list' 
														),
														array (
																'label' => 'Edit Source',
																'route' => 'source/edit',
																'action' => 'edit' 
														),
														array (
																'label' => 'Merge Source',
																'route' => 'source/merge',
																'action' => 'merge' 
														) 
												) 
										), 
								) 
						),
						array (
								'label' => 'Accounts',
								'route' => 'account',
								'pages' => array (
										array (
												'label' => 'Display Accounts',
												'route' => 'account',
												'action' => 'list' 
										),
										array (
												'label' => 'Display Accounts',
												'route' => 'account/list',
												'action' => 'list' 
										),
										array (
												'label' => 'Add Account',
												'route' => 'account/add',
												'action' => 'add' 
										),
										array (
												'label' => 'View Account',
												'route' => 'account/view',
												'action' => 'view',
												'visible' => true 
										),
										array (
												'label' => 'Edit Account',
												'route' => 'account/edit',
												'action' => 'edit',
												'visible' => true 
										),
										array (
												'label' => 'Delete Account',
												'route' => 'account/delete',
												'action' => 'delete',
												'visible' => true 
										),
										array (
												'label' => 'Confirm Account Deletion',
												'route' => 'account/confirm',
												'action' => 'confirm',
												'visible' => true 
										),
										array (
												'label' => 'View Account',
												'route' => 'account/application',
												'action' => 'view',
												'dynamic' => true,
												'pages' => array (
														array (
																'label' => 'View Leads',
																'route' => 'account/application/lead',
																'action' => 'list',
																'visible' => true 
														),
														array (
																'label' => 'View Leads',
																'route' => 'account/application/lead/list',
																'action' => 'list',
																'visible' => true 
														),
														array (
																'label' => 'API(s)',
																'route' => 'account/application/api',
																'action' => 'list',
																'visible' => true 
														),
														array (
																'label' => 'API(s)',
																'route' => 'account/application/api/list',
																'action' => 'list',
																'visible' => true 
														),
														array (
																'label' => 'Edit API(s)',
																'route' => 'account/application/api/edit',
																'action' => 'edit',
																'visible' => true 
														) 
												) 
										) 
								) 
						),
						array (
								'label' => 'Services',
								'route' => 'services',
								'pages' => array (
										array (
												'label' => 'TenStreet',
												'route' => 'services/tenstreet',
												'pages' => array (
														array (
																'label' => 'TenStreet Leads',
																'route' => 'services/tenstreet/list',
																'action' => 'list' 
														),
														array (
																'label' => 'View Lead',
																'route' => 'services/tenstreet/view',
																'action' => 'view' 
														),
														array (
																'label' => 'TenStreet',
																'route' => 'services/tenstreet/submit',
																'action' => 'submit' 
														) 
												) 
										),
										array (
												'label' => 'Email',
												'route' => 'services/email',
												'pages' => array (
														array (
																'label' => 'Email',
																'route' => 'services/email/list',
																'action' => 'list' 
														),
														array (
																'label' => 'View Lead',
																'route' => 'services/email/view',
																'action' => 'view' 
														),
														array (
																'label' => 'Submit Email',
																'route' => 'services/email/submit',
																'action' => 'submit' 
														) 
												) 
										) 
								) 
						),
						array (
								'label' => 'Options',
								'route' => 'api',
								'pages' => array (
										array (
												'label' => 'Display APIs',
												'route' => 'api/list',
												'action' => 'list' 
										),
										array (
												'label' => 'TenStreet',
												'route' => 'api/edit',
												'action' => 'edit',
												'params' => array (
														'id' => 1 
												) 
										),
										array (
												'label' => 'Email',
												'route' => 'api/edit',
												'action' => 'edit',
												'params' => array (
														'id' => 2 
												) 
										),
										array (
												'label' => 'WebWorks',
												'route' => 'api/edit',
												'action' => 'edit',
												'params' => array (
														'id' => 3 
												) 
										) 
								) 
						),
						array (
								'label' => 'Logs',
								'route' => 'event',
								'pages' => array (
										array (
												'label' => 'Display Log',
												'route' => 'event/list',
												'action' => 'list' 
										),
										array (
												'label' => 'View Event',
												'route' => 'event/view',
												'action' => 'view' 
										) 
								) 
						),
						array (
								'label' => 'Reports',
								'route' => 'report',
								'pages' => array (
										array (
												'label' => 'Index Manager',
												'route' => 'report/index',
												'action' => 'list' 
										),
										array (
												'label' => 'Display Reports',
												'route' => 'report',
												'action' => 'list' 
										),
										array (
												'label' => 'Display Reports',
												'route' => 'report/list',
												'action' => 'list' 
										),
										array (
												'label' => 'View Report',
												'route' => 'report/application',
												'action' => 'view',
												'dynamic' => true,
												'pages' => array (
														array (
																'label' => 'View Results',
																'route' => 'report/application/result/list',
																'action' => 'list',
																'pages' => array (
																		array (
																				'label' => 'View Result',
																				'route' => 'report/application/result/view',
																				'action' => 'view' 
																		) 
																) 
														) 
												) 
										),
										array (
												'label' => 'Create Report',
												'route' => 'report/add',
												'action' => 'add' 
										),
										array (
												'label' => 'View Report',
												'route' => 'report/view',
												'action' => 'view' 
										),
										array (
												'label' => 'Edit Report',
												'route' => 'report/edit',
												'action' => 'edit' 
										),
										array (
												'label' => 'Archive Report',
												'route' => 'report/delete',
												'action' => 'delete' 
										) 
								) 
						),
						array (
								'label' => 'Profile',
								'route' => 'zfcuser',
								'pages' => array (
										array (
												'label' => 'Your Profile',
												'route' => 'zfcuser',
												'action' => 'index' 
										),
										array (
												'label' => 'Your Profile',
												'route' => 'user',
												'action' => 'index' 
										),
										array (
												'label' => 'Register',
												'route' => 'zfcuser/register',
												'action' => 'register',
												'visible' => true 
										),
										array (
												'label' => 'Register',
												'route' => 'user/register',
												'action' => 'register',
												'visible' => true 
										),
										array (
												'label' => 'Login',
												'route' => 'zfcuser/login',
												'action' => 'login' 
										),
										array (
												'label' => 'Login',
												'route' => 'user/login',
												'action' => 'login' 
										),
										array (
												'label' => 'Change Email',
												'route' => 'zfcuser/changeemail',
												'action' => 'changeemail' 
										),
										array (
												'label' => 'Change Email',
												'route' => 'user/changeemail',
												'action' => 'changeemail' 
										),
										array (
												'label' => 'Change Password',
												'route' => 'zfcuser/changepassword',
												'action' => 'changepassword' 
										),
										array (
												'label' => 'Change Password',
												'route' => 'user/changepassword',
												'action' => 'changepassword' 
										),
										array (
												'label' => 'Logout',
												'route' => 'zfcuser/logout',
												'action' => 'logout',
												'resource' => 'navigation',
												'privilege' => 'logout' 
										),
										array (
												'label' => 'Logout',
												'route' => 'user/logout',
												'action' => 'logout',
												'resource' => 'navigation',
												'privilege' => 'logout' 
										) 
								) 
						) 
				) 
		) 
);
