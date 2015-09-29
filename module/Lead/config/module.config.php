<?php
return array(
		'controllers' => array(
				'invokables' => array(
						'Lead\Controller\Lead' => 'Lead\Controller\LeadController',
						'Lead\Controller\TenStreet' => 'Lead\Controller\TenStreetController',
						'Lead\Controller\Email' => 'Lead\Controller\EmailController',
						'Lead\Controller\Import' => 'Lead\Controller\ImportController',
						'Lead\Controller\Services' => 'Lead\Controller\ServicesController'
				),
				'factories' => array(
						'Lead\Controller\Rest' => 'Lead\Controller\Factory\RestControllerFactory'
				)
		),
		'doctrine' => array(
				'driver' => array(
						'Lead_driver' => array(
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'cache' => 'array',
								'paths' => array(
										__DIR__ . '/../src/Lead/Entity/'
								)
						),
						'orm_default' => array(
								'drivers' => array(
										'Lead\Entity' => 'Lead_driver'
								)
						)
				)
		),
		'router' => array(
				'routes' => array(
						'lead' => array(
								'type' => 'Literal',
								'options' => array(
										'route' => '/lead',
										'defaults' => array(
												'controller' => 'Lead\Controller\Lead',
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
																'controller' => 'Lead\Controller\Lead',
																'action' => 'list'
														)
												)
										),
										'add' => array(
												'type' => 'Literal',
												'options' => array(
														'route' => '/add',
														'defaults' => array(
																'controller' => 'Lead\Controller\Lead',
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
																'controller' => 'Lead\Controller\Lead',
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
																'controller' => 'Lead\Controller\Lead',
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
																'controller' => 'Lead\Controller\Lead',
																'action' => 'delete',
																'id' => 0
														)
												)
										),
										'export' => array(
												'type' => 'Literal',
												'options' => array(
														'route' => '/export',
														'defaults' => array(
																'controller' => 'Lead\Controller\Lead',
																'action' => 'export'
														)
												)
										)
								)
						),
						'services' => array(
								'type' => 'Literal',
								'options' => array(
										'route' => '/services',
										'defaults' => array(
												'controller' => 'Lead\Controller\Lead',
												'action' => 'list'
										)
								),
								'may_terminate' => true,
								'child_routes' => array(
										'tenstreet' => array(
												'type' => 'Literal',
												'options' => array(
														'route' => '/tenstreet',
														'defaults' => array(
																'controller' => 'Lead\Controller\TenStreet',
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
																				'controller' => 'Lead\Controller\TenStreet',
																				'action' => 'list'
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
																				'controller' => 'Lead\Controller\TenStreet',
																				'action' => 'view',
																				'id' => 0
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
																				'controller' => 'Lead\Controller\TenStreet',
																				'action' => 'edit',
																				'id' => 0
																		)
																)
														),
														'submit' => array(
																'type' => 'Segment',
																'options' => array(
																		'route' => '/submit[/:id]',
																		'defaults' => array(
																				'controller' => 'Lead\Controller\TenStreet',
																				'action' => 'submit',
																				'id' => 0
																		)
																)
														)
												)
										),
										'email' => array(
												'type' => 'Literal',
												'options' => array(
														'route' => '/email',
														'defaults' => array(
																'controller' => 'Lead\Controller\Email',
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
																				'controller' => 'Lead\Controller\Email',
																				'action' => 'list'
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
																				'controller' => 'Lead\Controller\Email',
																				'action' => 'view',
																				'id' => 0
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
																				'controller' => 'Lead\Controller\Email',
																				'action' => 'edit',
																				'id' => 0
																		)
																)
														),
														'submit' => array(
																'type' => 'Segment',
																'options' => array(
																		'route' => '/submit[/:id]',
																		'defaults' => array(
																				'controller' => 'Lead\Controller\Email',
																				'action' => 'submit',
																				'id' => 0
																		)
																)
														)
												)
										),
										'process' => array(
												'type' => 'Segment',
												'options' => array(
														'route' => '/process[/:id]',
														'constraints' => array(
																'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
																'id' => '[0-9]+'
														),
														'defaults' => array(
																'controller' => 'Lead\Controller\Services',
																'action' => 'process',
																'id' => 0
														)
												)
										)
								)
						),
						'import' => array(
								'type' => 'Literal',
								'options' => array(
										'route' => '/import',
										'defaults' => array(
												'controller' => 'Lead\Controller\Import',
												'action' => 'import'
										)
								)
						),
						'rest-api' => array(
								'type' => 'Segment',
								'options' => array(
										'route' => '/rest-api[/:action][/:id]',
										'constraints' => array(
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'id' => '[0-9]+'
										),
										'defaults' => array(
												'controller' => 'Lead\Controller\Rest',
												'action' => 'index'
										)
								)
						)
				)
		),
		'view_manager' => array(
				'template_path_stack' => array(
						'Lead' => __DIR__ . '/../view'
				),
				'strategies' => array(
						'ViewJsonStrategy'
				)
		),
		'service_manager' => array(
				'factories' => array(
						'Lead\Form\AddFormFactory' => 'Lead\Form\Factory\AddFormFactory'
				)
		),
		'form_elements' => array(
				'invokables' => array(
						'Lead\Form\AddForm' => 'Lead\Form\AddForm'
				),
				'factories' => array(
						'Lead\Form\FilterForm' => 'Lead\Form\Factory\FilterFormFactory',
						'Lead\Form\ListForm' => 'Lead\Form\Factory\ListFormFactory',
						'Lead\Form\ImportForm' => 'Lead\Form\Factory\ImportFormFactory'
				)
		)
);
