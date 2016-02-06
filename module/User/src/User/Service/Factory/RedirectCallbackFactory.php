<?php

namespace User\Service\Factory;

use Zend\Mvc\Application;
use Zend\Mvc\Router\RouteInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use User\Service\Callback\RedirectCallback;
use ZfcUser\Options\ModuleOptions;

class RedirectCallbackFactory implements FactoryInterface {

	/**
	 * Create service
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return mixed
	 */
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		/* @var $router RouteInterface */
		$router = $serviceLocator->get('Router');
		
		/* @var $application Application */
		$application = $serviceLocator->get('Application');
		
		/* @var $options ModuleOptions */
		$options = $serviceLocator->get('zfcuser_module_options');
		
		return new RedirectCallback($application, $router, $options);
	}
}
