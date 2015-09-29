<?php
return array(
		'controllers' => array(
				'invokables' => array(
						'Account\Controller\Account' => 'Account\Controller\AccountController',
						'Account\Controller\Lead' => 'Account\Controller\LeadController',
						'Account\Controller\Api' => 'Account\Controller\ApiController'
				)
		),
		'doctrine' => array(
				'driver' => array(
						'Account_driver' => array(
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'cache' => 'array',
								'paths' => array(
										__DIR__ . '/../src/Account/Entity/'
								)
						),
						'orm_default' => array(
								'drivers' => array(
										'Account\Entity' => 'Account_driver'
								)
						)
				)
		),
		'router' => array(
				'routes' => array(
						'account' => array(
								'type' => 'Literal',
								'options' => array(
										'route' => '/account',
										'defaults' => array(
												'controller' => 'Account\Controller\Account',
												'action' => 'list'
										)
								),
								'may_terminate' => true,
								'child_routes' => array(
										'list' => array(
												'type' => 'Literal',
												'options' => array(
														'route' => '/list',
														'defaults' => array(
																'controller' => 'Account\Controller\Account',
																'action' => 'list'
														)
												)
										),
										'add' => array(
												'type' => 'Literal',
												'options' => array(
														'route' => '/add',
														'defaults' => array(
																'controller' => 'Account\Controller\Account',
																'action' => 'add'
														)
												)
										),
										'edit' => array(
												'type' => 'Segment',
												'options' => array(
														'route' => '/edit[/:id]',
														'constraints' => array(
																'id' => '[0-9]+'
														),
														'defaults' => array(
																'controller' => 'Account\Controller\Account',
																'action' => 'edit',
																'id' => 0
														)
												)
										),
										'view' => array(
												'type' => 'Segment',
												'options' => array(
														'route' => '/view[/:id]',
														'constraints' => array(
																'id' => '[0-9]+'
														),
														'defaults' => array(
																'controller' => 'Account\Controller\Account',
																'action' => 'view',
																'id' => 0
														)
												)
										),
										'delete' => array(
												'type' => 'Segment',
												'options' => array(
														'route' => '/delete[/:id]',
														'constraints' => array(
																'id' => '[0-9]+'
														),
														'defaults' => array(
																'controller' => 'Account\Controller\Account',
																'action' => 'delete',
																'id' => 0
														)
												)
										),
										'application' => array(
												'type' => 'Segment',
												'options' => array(
														'route' => '/[:id]',
														'constraints' => array(
																'id' => '[0-9]+'
														),
														'defaults' => array(
																'controller' => 'Account\Controller\Account',
																'action' => 'view',
																'id' => 0
														)
												),
												'may_terminate' => true,
												'child_routes' => array(
														'lead' => array(
																'type' => 'Literal',
																'options' => array(
																		'route' => '/lead',
																		'defaults' => array(
																				'controller' => 'Account\Controller\Lead',
																				'action' => 'list'
																		)
																),
																'may_terminate' => true,
																'child_routes' => array(
																		'list' => array(
																				'type' => 'Literal',
																				'options' => array(
																						'route' => '/list',
																						'defaults' => array(
																								'controller' => 'Account\Controller\Lead',
																								'action' => 'list'
																						)
																				)
																		),
																		'add' => array(
																				'type' => 'Literal',
																				'options' => array(
																						'route' => '/add',
																						'defaults' => array(
																								'controller' => 'Account\Controller\Lead',
																								'action' => 'add'
																						)
																				)
																		),
																		'edit' => array(
																				'type' => 'Segment',
																				'options' => array(
																						'route' => '/edit',
																						'defaults' => array(
																								'controller' => 'Account\Controller\Lead',
																								'action' => 'edit'
																						)
																				)
																		)
																)
														),
														'api' => array(
																'type' => 'Literal',
																'options' => array(
																		'route' => '/api',
																		'defaults' => array(
																				'controller' => 'Account\Controller\Api',
																				'action' => 'list'
																		)
																),
																'may_terminate' => true,
																'child_routes' => array(
																		'list' => array(
																				'type' => 'Literal',
																				'options' => array(
																						'route' => '/list',
																						'defaults' => array(
																								'controller' => 'Account\Controller\Api',
																								'action' => 'list'
																						)
																				)
																		),
																		'add' => array(
																				'type' => 'Literal',
																				'options' => array(
																						'route' => '/add',
																						'defaults' => array(
																								'controller' => 'Account\Controller\Api',
																								'action' => 'add'
																						)
																				)
																		),
																		'edit' => array(
																				'type' => 'Segment',
																				'options' => array(
																						'route' => '/edit',
																						'defaults' => array(
																								'controller' => 'Account\Controller\Api',
																								'action' => 'edit'
																						)
																				)
																		),
																		'delete' => array(
																				'type' => 'Segment',
																				'options' => array(
																						'route' => '/delete',
																						'defaults' => array(
																								'controller' => 'Account\Controller\Api',
																								'action' => 'delete'
																						)
																				)
																		)
																)
														)
												)
										)
								)
						)
				)
		),
		'view_manager' => array(
				'template_path_stack' => array(
						'Account' => __DIR__ . '/../view'
				)
		),
		'service_manager' => array(
				'invokables' => array(
						'IdGenerator' => 'Account\Utility\IdGenerator'
				),
				'factories' => array(
						'Account\Form\EditFormFactory' => 'Account\Form\Factory\EditFormFactory'
				)
		),
		'form_elements' => array(
				'invokables' => array(
						'Account\Form\EditForm' => 'Account\Form\EditForm'
				),
				'factories' => array(
						'Account\Form\FilterForm' => 'Account\Form\Factory\FilterFormFactory',
						'Account\Form\ListForm' => 'Account\Form\Factory\ListFormFactory'
				)
		)
);
