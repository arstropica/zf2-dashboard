<?php
return array(
		'controllers' => array(
				'invokables' => array(
						'Api\Controller\Api' => 'Api\Controller\ApiController'
				)
		),
		'doctrine' => array(
				'driver' => array(
						'Api_driver' => array(
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'cache' => 'array',
								'paths' => array(
										__DIR__ . '/../src/Api/Entity/'
								)
						),
						'orm_default' => array(
								'drivers' => array(
										'Api\Entity' => 'Api_driver'
								)
						)
				)
		),
		'router' => array(
				'routes' => array(
						'api' => array(
								'type' => 'Literal',
								'options' => array(
										'route' => '/api',
										'defaults' => array(
												'controller' => 'Api\Controller\Api',
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
																'controller' => 'Api\Controller\Api',
																'action' => 'list'
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
																'controller' => 'Api\Controller\Api',
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
																'controller' => 'Api\Controller\Api',
																'action' => 'view',
																'id' => 0
														)
												)
										)
								)
						)
				)
		),
		'view_manager' => array(
				'template_path_stack' => array(
						'Api' => __DIR__ . '/../view'
				)
		),
		'service_manager' => array(
				'factories' => array(
						'Api\Form\EditFormFactory' => 'Api\Form\Factory\EditFormFactory'
				)
		),
		'form_elements' => array(
				'invokables' => array(
						'Api\Form\EditForm' => 'Api\Form\EditForm'
				),
				'factories' => array(
						'Api\Form\FilterForm' => 'Api\Form\Factory\FilterFormFactory',
						'Api\Form\ListForm' => 'Api\Form\Factory\ListFormFactory'
				)
		)
);
