<?php
return array(
		'controllers' => array(
				'factories' => array(
						'TenStreet\Controller\SoapClient' => 'TenStreet\Controller\Factory\SoapClientControllerFactory'
				)
		),
		'router' => array(
				'routes' => array(
						'ten-street' => array(
								'type' => 'Segment',
								'options' => array(
										// Change this to something specific to
										// your module
										'route' => '/soap[/:action][/:id]',
										'constraints' => array(
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'id' => '[0-9]+'
										),
										'defaults' => array(
												'__NAMESPACE__' => 'TenStreet\Controller',
												'controller' => 'SoapClient',
												'action' => 'index'
										)
								)
						)
				)
		),
		'view_manager' => array(
				'template_path_stack' => array(
						'TenStreet' => __DIR__ . '/../view'
				),
				'strategies' => array(
						'ViewJsonStrategy'
				)
		),
		'service_manager' => array(
				'factories' => array(
						'TenStreet\Service\PostClientData' => 'TenStreet\Service\Factory\PostClientDataServiceFactory'
				)
		),
		'hydrators' => array(
				'factories' => array(
						'ApplicationDataHydrator' => 'TenStreet\Hydrator\Factory\ApplicationDataHydratorFactory',
						'PersonalDataHydrator' => 'TenStreet\Hydrator\Factory\PersonalDataHydratorFactory',
						'TenStreetDataHydrator' => 'TenStreet\Hydrator\Factory\TenStreetDataHydratorFactory'
				)
		),
		'log' => array(
				'file' => 'logs/' . date('Y-m') . '.log'
		),
		'config_glob_paths' => array(
				'config/autoload/{{,*.}global,{,*.}local}.php'
		)
);
