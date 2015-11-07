<?php
$env = getenv('APPLICATION_ENV') ?  : 'development';
return array(
		'controllers' => array(
				'invokables' => array(
						'User\Controller\User' => 'User\Controller\UserController',
				)
		),
		'router' => array(
				'routes' => array(
						'gapi' => array(
								'type' => 'Literal',
								'options' => array(
										'route' => '/gapi',
										'defaults' => array(
												'controller' => 'User\Controller\User',
												'action' => 'index'
										)
								),
								'may_terminate' => true,
								'child_routes' => array(
										'auth' => array(
												'type' => 'Literal',
												'options' => array(
														'route' => '/auth',
														'defaults' => array(
																'controller' => 'User\Controller\User',
																'action' => 'auth'
														)
												)
										),
										'token' => array(
												'type' => 'Literal',
												'options' => array(
														'route' => '/token',
														'defaults' => array(
																'controller' => 'User\Controller\User',
																'action' => 'token'
														)
												),
												'may_terminate' => true,
												'child_routes' => array(
														'exchange' => array(
																'type' => 'Literal',
																'options' => array(
																		'route' => '/exchange',
																		'defaults' => array(
																				'controller' => 'User\Controller\User',
																				'action' => 'exchange'
																		)
																)
														),
														'refresh' => array(
																'type' => 'Literal',
																'options' => array(
																		'route' => '/refresh',
																		'defaults' => array(
																				'controller' => 'User\Controller\User',
																				'action' => 'refresh'
																		)
																)
														),
														'valid' => array(
																'type' => 'Literal',
																'options' => array(
																		'route' => '/valid',
																		'defaults' => array(
																				'controller' => 'User\Controller\User',
																				'action' => 'valid'
																		)
																)
														),
														'revoke' => array(
																'type' => 'Literal',
																'options' => array(
																		'route' => '/revoke',
																		'defaults' => array(
																				'controller' => 'User\Controller\User',
																				'action' => 'revoke'
																		)
																)
														),
												),
										),
								),
						),
				),
		),
		'view_manager' => array(
				'display_exceptions' => true, // ($env == 'production') ? false : true,
				'template_map' => array(
						'error/403' => __DIR__ . '/../view/error/403.phtml',
						'oauth/receive-code' => __DIR__ .
								 '/../view/zf/auth/receive-code.phtml'
				),
				'template_path_stack' => array(
						'zfcuser' => __DIR__ . '/../view',
						'User' => __DIR__ . '/../view'
				),
				'strategies' => array(
					'ViewJsonStrategy',
				),
		),
		'doctrine' => array(
				'driver' => array(
						'User_driver' => array(
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'cache' => 'array',
								'paths' => array(
										__DIR__ . '/../src/User/Entity/'
								)
						),
						'orm_default' => array(
								'drivers' => array(
										'User\Entity' => 'User_driver'
								)
						)
				)
		),
		'zfcuser' => array(
				'table_name' => 'user',
				'new_user_default_role' => 'user',
				// telling ZfcUser to use our own class
				'user_entity_class' => 'User\Entity\User',
				// telling ZfcUserDoctrineORM to skip the entities it defines
				'enable_default_entities' => false,
				'enable_registration' => false,
				'enable_username' => true,
				'auth_adapters' => array(
						100 => 'ZfcUser\Authentication\Adapter\Db'
				),
				'use_redirect_parameter_if_present' => true,
				'auth_identity_fields' => array(
						'email'
				)
		),
		
		'bjyauthorize' => array(
				'default_role' => 'guest',
				// Using the authentication identity provider, which basically
				// reads the roles from the auth service's identity
				'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider',
				
				'authenticated_role' => 'user',
				
				'role_providers' => array(
						// using an object repository (entity repository) to
						// load all roles into our ACL
						'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => array(
								'object_manager' => 'doctrine.entitymanager.orm_default',
								'role_entity_class' => 'User\Entity\Role'
						)
				),
				
				'resource_providers' => array(
						'BjyAuthorize\Provider\Resource\Config' => array(
								'navigation' => [],
								'resource' => []
						)
				),
				
				'rule_providers' => array(
						'BjyAuthorize\Provider\Rule\Config' => array(
								'allow' => array(
										array(
												[
														'user',
														'moderator',
														'administrator'
												],
												'navigation',
												'logout'
										),
										array(
												[
														'user',
														'moderator',
														'administrator'
												],
												'navigation',
												'display'
										),
										array(
												[
														'administrator'
												],
												'resource',
												'delete'
										)
								),
								'deny' => array(
										array(
												[
														'user',
														'moderator',
														'administrator'
												],
												'navigation',
												'login'
										),
								)
						)
				),
				
				'unauthorized_strategy' => 'User\View\UnauthorizedStrategy',
				
				'guards' => array(
						'BjyAuthorize\Guard\Controller' => array(
								array(
										'controller' => 'zfcuser',
										'action' => array(
												'index'
										),
										'roles' => array(
												'guest',
												'user',
												'moderator',
												'administrator'
										)
								),
								array(
										'controller' => 'zfcuser',
										'action' => array(
												'login',
												'authenticate'
										),
										'roles' => array(
												'guest',
												'user',
												'moderator',
												'administrator'
										)
								),
								array(
										'controller' => 'zfcuser',
										'action' => array(
												'register'
										),
										'roles' => array()
								),
								// 'guest'
								
								array(
										'controller' => 'zfcuser',
										'action' => array(
												'logout',
												'changeemail',
												'changepassword'
										),
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Application\Controller\Index',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'User\Controller\User',
										'roles' => array(
												'guest'
										)
								),
								
								array(
										'controller' => 'Lead\Controller\Lead',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Lead\Controller\Import',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Lead\Controller\Rest',
										'roles' => array(
												'guest'
										)
								),
								
								array(
										'controller' => 'Lead\Controller\TenStreet',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Lead\Controller\Email',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Lead\Controller\Services',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Lead\Controller\Attribute',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Account\Controller\Account',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Account\Controller\Lead',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Account\Controller\Api',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Api\Controller\Api',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Application\Controller\Navigation',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'Rest\Controller\Index',
										'roles' => array(
												'guest'
										)
								),
								
								array(
										'controller' => 'TenStreet\Controller\SoapClient',
										'roles' => array(
												'guest'
										)
								),
								
								array(
										'controller' => 'Event\Controller\Event',
										'roles' => array(
												'user',
												'moderator',
												'administrator'
										)
								),
								
								array(
										'controller' => 'ZF\OAuth2\Controller\Auth',
										'roles' => array(
												'guest'
										)
								)
						)
				),
				'service_manager' => array(
						'invokables' => array(
								'User\View\UnauthorizedStrategy' => 'User\View\UnauthorizedStrategy'
						),
						'factories' => array(
								'User\Entity\Role' => 'User\Entity\Role',
								'User\Entity\User' => 'User\Entity\User',
								'User\Authentication\Adapter\OAuth2Adapter' => 'User\Authentication\Adapter\Factory\OAuth2AdapterFactory'
						)
				)
		)
);
