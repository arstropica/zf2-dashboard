<?php
return array (
		'controllers' => array (
				'invokables' => array (
						'Event\Controller\Event' => 'Event\Controller\EventController' 
				) 
		),
		'doctrine' => array (
				'driver' => array (
						'Event_driver' => array (
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'cache' => 'array',
								'paths' => array (
										__DIR__ . '/../src/Event/Entity/' 
								) 
						),
						'orm_default' => array (
								'drivers' => array (
										'Event\Entity' => 'Event_driver' 
								) 
						) 
				) 
		),
		'router' => array (
				'routes' => array (
						'event' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/event',
										'defaults' => array (
												'controller' => 'Event\Controller\Event',
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
																'controller' => 'Event\Controller\Event',
																'action' => 'list' 
														) 
												) 
										),
										'export' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/export',
														'defaults' => array (
																'controller' => 'Event\Controller\Event',
																'action' => 'export' 
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
																'controller' => 'Event\Controller\Event',
																'action' => 'view',
																'id' => 0 
														) 
												) 
										) 
								) 
						) 
				) 
		),
		'view_manager' => array (
				'template_path_stack' => array (
						'Event' => __DIR__ . '/../view' 
				) 
		),
		'form_elements' => array (
				'factories' => array (
						'Event\Form\FilterForm' => 'Event\Form\Factory\FilterFormFactory' 
				) 
		) 
);
