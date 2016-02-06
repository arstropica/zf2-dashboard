<?php
return array (
		'controllers' => array (
				'factories' => array (
						'WebWorks\Controller\XMLClient' => 'WebWorks\Controller\Factory\XMLControllerFactory' 
				) 
		),
		'router' => array (
				'routes' => array (
						'webworks' => array (
								'type' => 'Segment',
								'options' => array (
										// Change this to something specific to
										// your module
										'route' => '/webworks[/:action][/:id]',
										'constraints' => array (
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'id' => '[0-9]+' 
										),
										'defaults' => array (
												'__NAMESPACE__' => 'WebWorks\Controller',
												'controller' => 'XMLClient',
												'action' => 'index' 
										) 
								) 
						) 
				) 
		),
		'view_manager' => array (
				'template_path_stack' => array (
						'WebWorks' => __DIR__ . '/../view' 
				),
				'strategies' => array (
						'ViewJsonStrategy' 
				) 
		),
		'service_manager' => array (
				'factories' => array (
						'WebWorks\Service\ImportXML' => 'WebWorks\Service\Factory\ImportXMLServiceFactory' 
				) 
		),
		'hydrators' => array (
				'factories' => array (
						'WebWorks\Hydrator\ApplicationDataHydrator' => 'WebWorks\Hydrator\Factory\ApplicationDataHydratorFactory',
						'WebWorks\Hydrator\PersonalDataHydrator' => 'WebWorks\Hydrator\Factory\PersonalDataHydratorFactory',
						'WebWorks\Hydrator\WebWorksDataHydrator' => 'WebWorks\Hydrator\Factory\WebWorksDataHydratorFactory' 
				) 
		),
		'log' => array (
				'file' => 'logs/' . date('Y-m') . '.log' 
		),
		'config_glob_paths' => array (
				'config/autoload/{{,*.}global,{,*.}local}.php' 
		) 
);
