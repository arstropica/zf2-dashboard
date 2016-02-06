<?php
$env = getenv('APPLICATION_ENV') ?  : 'development';
return array (
		'controllers' => array (
				'invokables' => array (
						'Agent\Controller\Agent' => 'Agent\Controller\AgentController' 
				) 
		),
		'doctrine' => array (
				'driver' => array (
						'Agent_driver' => array (
								'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
								'cache' => 'array',
								'paths' => array (
										__DIR__ . '/../src/Agent/Entity/' 
								) 
						),
						'orm_default' => array (
								'drivers' => array (
										'Agent\Entity' => 'Agent_driver' 
								) 
						) 
				) 
		),
		'router' => array (
				'routes' => array (
						'agent' => array (
								'type' => 'Literal',
								'options' => array (
										'route' => '/agent',
										'defaults' => array (
												'controller' => 'Agent\Controller\Agent',
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
																'controller' => 'Agent\Controller\Agent',
																'action' => 'list' 
														) 
												) 
										),
										'add' => array (
												'type' => 'Literal',
												'options' => array (
														'route' => '/add',
														'defaults' => array (
																'controller' => 'Agent\Controller\Agent',
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
																'controller' => 'Agent\Controller\Agent',
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
																'controller' => 'Agent\Controller\Agent',
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
																'controller' => 'Agent\Controller\Agent',
																'action' => 'delete',
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
						'Agent' => __DIR__ . '/../view' 
				) 
		),
		'service_manager' => array (
				'invokables' => array (),
				'factories' => array () 
		),
		'form_elements' => array (
				'invokables' => array (
						'Agent\Form\Fieldset\AgentCriterionFieldset' => 'Agent\Form\Fieldset\AgentCriterionFieldset',
						'Agent\Form\Fieldset\AgentCriterionValueFieldset' => 'Agent\Form\Fieldset\AgentCriterionValueFieldset' 
				),
				'factories' => array () 
		) 
);
