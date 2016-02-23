<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/WebWorks for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace WebWorks;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use WebWorks\Hydrator\WebWorksHydrator;
use WebWorks\Mapper\WebWorksDataMapper;
use WebWorks\Hydrator\Strategy\PhoneNumberStrategy;
use WebWorks\Hydrator\Strategy\MapperNamingStrategy;
use WebWorks\Event\Listener\ImportXMLListener;

class Module implements AutoloaderProviderInterface {

	public function getAutoloaderConfig()
	{
		return array (
				'Zend\Loader\ClassMapAutoloader' => array (
						__DIR__ . '/autoload_classmap.php' 
				),
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => array (
								// if we're in a namespace deeper than one level
								// we need to fix the \ in the path
								__NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__) 
						) 
				) 
		);
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function onBootstrap(MvcEvent $e)
	{
		// You may not need to do this if you're doing it elsewhere in your
		// application
		$eventManager = $e->getApplication()
			->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		$sharedEvents = $e->getApplication()
			->getEventManager()
			->getSharedManager();
		$sharedEvents->attach(__NAMESPACE__, 'dispatch', function ($e) {
			$result = $e->getResult();
			$result->setTerminal(true);
		});
		
		$sm = $e->getApplication()
			->getServiceManager();
		$logger = $sm->get('Logger');
		$eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, function (MvcEvent $e) use($logger) {
			$logger->info('An Exception has occurred. ' . $e->getResult()->exception->getMessage());
		}, -200);
		
		$eventManager->attach(new ImportXMLListener($sm));
	}

	public function getControllerConfig()
	{
		return array (
				'factories' => array (
						'WebWorks\Controller\XMLClient' => 'WebWorks\Controller\Factory\XMLControllerFactory' 
				) 
		);
	}

	public function getServiceConfig()
	{
		return array (
				'factories' => array (
						'OAuth2Server' => function ($sm) {
							return $sm->get('OAuth2Factory');
						},
						'OAuth2Factory' => function ($sm) {
							$oauth2Factory = $sm->get('ZF\OAuth2\Service\OAuth2Server');
							$sm->setFactory('OAuth2FactoryInstance', $oauth2Factory);
							return $sm->get('OAuth2FactoryInstance');
						},
						'WebWorks\Hydrator\PersonNameHydrator' => function ($sm) {
							return new WebWorksHydrator(false);
						},
						'WebWorks\Hydrator\PostalAddressHydrator' => function ($sm) {
							return new WebWorksHydrator(false);
						},
						'WebWorks\Hydrator\DisplayFieldHydrator' => function ($sm) {
							return new WebWorksHydrator(false);
						},
						'WebWorks\Hydrator\LicenseHydrator' => function ($sm) {
							return new WebWorksHydrator(false);
						},
						'WebWorks\Hydrator\ContactDataHydrator' => function ($sm) {
							$hydrator = new WebWorksHydrator(false);
							$hydrator->addStrategy('PrimaryPhone', new PhoneNumberStrategy());
							$hydrator->setNamingStrategy(new MapperNamingStrategy(array (
									'attributes' => '@attributes' 
							)));
							
							return $hydrator;
						},
						'WebWorks\Hydrator\AuthenticationHydrator' => function ($sm) {
							return new WebWorksHydrator(false);
						},
						'WebWorksDataMapper' => function ($sm) {
							$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
							return new WebWorksDataMapper($sm, $dbAdapter);
						},
						'Logger' => function ($sm) {
							$config = $sm->get('config');
							$logger = new \Zend\Log\Logger();
							if (isset($config ['log'] ['file']) && is_writable(dirname($config ['log'] ['file']))) {
								$writer = new \Zend\Log\Writer\Stream($config ['log'] ['file']);
								$logger->addWriter($writer);
							}
							return $logger;
						} 
				) 
		);
	}
}
