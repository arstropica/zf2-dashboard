<?php
return array (
		'controllers' => array (
				'invokables' => array (
						'Report\Controller\Report' => 'Report\Controller\ReportController',
						'Report\Controller\Index' => 'Report\Controller\IndexController',
						'Report\Controller\Result' => 'Report\Controller\ResultController' 
				) 
		),
		'doctrine' => array (
				'driver' => array (
						'Report_driver' => array (
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'cache' => 'array',
								'paths' => array (
										__DIR__ . '/../src/Report/Entity/' 
								) 
						),
						'orm_default' => array (
								'drivers' => array (
										'Report\Entity' => 'Report_driver' 
								) 
						) 
				),
				'configuration' => array (
						'orm_default' => array (
								'string_functions' => array (
										'FIELD' => 'DoctrineExtensions\Query\Mysql\Field' 
								) 
						) 
				) 
		),
		'router' => array (
				'routes' => array (
						'report' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/report',
										'defaults' => array (
												'controller' => 'Report\Controller\Report',
												'action' => 'list' 
										) 
								),
								'may_terminate' => true,
								'child_routes' => array (
										'index' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/index',
														'defaults' => array (
																'controller' => 'Report\Controller\Index',
																'action' => 'list' 
														) 
												) 
										),
										'build' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/ajax/build',
														'defaults' => array (
																'controller' => 'Report\Controller\Index',
																'action' => 'build' 
														) 
												) 
										),
										'notify' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/ajax/notify',
														'defaults' => array (
																'controller' => 'Report\Controller\Index',
																'action' => 'notify' 
														) 
												) 
										),
										'stats' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/ajax/stats[/:index]',
														'defaults' => array (
																'controller' => 'Report\Controller\Index',
																'action' => 'stats' 
														) 
												) 
										),
										'get' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/ajax/get[/:index][/:type]',
														'defaults' => array (
																'controller' => 'Report\Controller\Index',
																'action' => 'get',
																'index' => 'reports',
																'type' => 'all' 
														) 
												) 
										),
										'data' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/ajax/data[/:index][/:type]',
														'defaults' => array (
																'controller' => 'Report\Controller\Index',
																'action' => 'data',
																'index' => 'reports',
																'type' => 'all' 
														) 
												) 
										),
										'attribute' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/ajax/attribute[/:id]',
														'defaults' => array (
																'controller' => 'Report\Controller\Index',
																'action' => 'attribute',
																'id' => 0 
														) 
												) 
										),
										'test' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/test',
														'defaults' => array (
																'controller' => 'Report\Controller\Index',
																'action' => 'test' 
														) 
												) 
										),
										'update' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/update',
														'defaults' => array (
																'controller' => 'Report\Controller\Index',
																'action' => 'update' 
														) 
												) 
										),
										'list' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/list',
														'defaults' => array (
																'controller' => 'Report\Controller\Report',
																'action' => 'list' 
														) 
												) 
										),
										'add' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/add',
														'defaults' => array (
																'controller' => 'Report\Controller\Report',
																'action' => 'add' 
														) 
												) 
										),
										'edit' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/edit[/:id]',
														'constraints' => array (
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Report\Controller\Report',
																'action' => 'edit',
																'id' => 0 
														) 
												) 
										),
										'view' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/view[/:id]',
														'constraints' => array (
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Report\Controller\Report',
																'action' => 'view',
																'id' => 0 
														) 
												) 
										),
										'delete' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/delete[/:id]',
														'constraints' => array (
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Report\Controller\Report',
																'action' => 'delete',
																'id' => 0 
														) 
												) 
										),
										'application' => array (
												'type' => 'Segment',
												'options' => array (
														'route' => '/[:id]',
														'constraints' => array (
																'id' => '[0-9]+' 
														),
														'defaults' => array (
																'controller' => 'Report\Controller\Report',
																'action' => 'view',
																'id' => 0 
														) 
												),
												'may_terminate' => true,
												'child_routes' => array (
														'result' => array (
																'type' => 'Literal',
																'options' => array (
																		'route' => '/result',
																		'defaults' => array (
																				'controller' => 'Report\Controller\Result',
																				'action' => 'list' 
																		) 
																),
																'may_terminate' => true,
																'child_routes' => array (
																		'list' => array (
																				'type' => 'Literal',
																				'options' => array (
																						'route' => '/list',
																						'defaults' => array (
																								'controller' => 'Report\Controller\Result',
																								'action' => 'list' 
																						) 
																				) 
																		),
																		'export' => array (
																				'type' => 'Literal',
																				'options' => array (
																						'route' => '/export',
																						'defaults' => array (
																								'controller' => 'Report\Controller\Result',
																								'action' => 'export' 
																						) 
																				) 
																		),
																		'view' => array (
																				'type' => 'Segment',
																				'options' => array (
																						'route' => '/view[/:lead]',
																						'constraints' => array (
																								'lead' => '[0-9]+' 
																						),
																						'defaults' => array (
																								'controller' => 'Report\Controller\Result',
																								'action' => 'view',
																								'lead' => 0 
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
		'controller_plugins' => array (
				'factories' => array (
						'highChart' => 'Report\Controller\Plugin\Factory\HighChartFactory' 
				) 
		),
		'view_manager' => array (
				'template_path_stack' => array (
						'Report' => __DIR__ . '/../view' 
				),
				'strategies' => array (
						'ViewJsonStrategy' 
				) 
		),
		'service_manager' => array (
				'invokables' => array (),
				'factories' => array (
						'Report\Form\AddFormFactory' => 'Report\Form\Factory\AddFormFactory',
						'Report\Form\EditFormFactory' => 'Report\Form\Factory\EditFormFactory' 
				) 
		),
		'form_elements' => array (
				'invokables' => array (
						'Report\Form\AddForm' => 'Report\Form\AddForm',
						'Report\Form\EditForm' => 'Report\Form\EditForm' 
				),
				'factories' => array (
						'Report\Form\ListForm' => 'Report\Form\Factory\ListFormFactory',
						'Report\Form\FilterForm' => 'Report\Form\Factory\FilterFormFactory' 
				) 
		) 
);
