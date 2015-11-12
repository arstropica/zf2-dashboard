<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Lead for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Lead;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Lead\Event\Listener\LeadListener;
use Lead\Event\Listener\AttributeListener;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Configuration as DoctrineConfig;
use Doctrine\ORM\Cache\DefaultCacheFactory;

class Module implements AutoloaderProviderInterface
{

	public function getAutoloaderConfig ()
	{
		return array(
				'Zend\Loader\ClassMapAutoloader' => array(
						__DIR__ . '/autoload_classmap.php'
				),
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								// if we're in a namespace deeper than one level
								// we need to fix the \ in the path
								__NAMESPACE__ => __DIR__ . '/src/' .
										 str_replace('\\', '/', __NAMESPACE__)
						)
				)
		);
	}

	public function getConfig ()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function onBootstrap (MvcEvent $e)
	{
		// You may not need to do this if you're doing it elsewhere in your
		// application
		$eventManager = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		
		$sm = $e->getApplication()->getServiceManager();
		
		$em = $sm->get('Doctrine\ORM\EntityManager');
		
		// $this->configSecondLevelCache($sm, 'Lead\Cache\Lead');
		
		// Register Entity Listeners
		/* $config = $this->getConfig();
		$invokables = isset($config['service_manager']['invokables']) ? $config['service_manager']['invokables'] : [];
		foreach ($invokables as $invokable) {
			// Verify the listener namespaces
			if (strpos($invokable, 'Lead\Entity\Listener') !== false) {
				$em->getConfiguration()
					->getEntityListenerResolver()
					->register($sm->get($invokable));
			}
		} */
		
		// Register Event Listeners
		$eventManager->attach(new LeadListener($sm));
		$eventManager->attach(new AttributeListener($sm));
	}

	/**
	 *
	 * @param ServiceLocatorInterface $sm        	
	 * @param string $region        	
	 */
	public function configSecondLevelCache ($sm, $region)
	{
		$cache = $sm->get('doctrine.cache.redis');
		$config = new DoctrineConfig();
		$config->setSecondLevelCacheEnabled();
		
		$cacheConfig = $config->getSecondLevelCacheConfiguration();
		
		$regionConfig = $cacheConfig->getRegionsConfiguration();
		$regionConfig->setLifetime($region, 3600);
		$regionConfig->setDefaultLifetime(7200); // Default time to live; secs
		$factory = new DefaultCacheFactory($regionConfig, $cache);
		$cacheConfig->setCacheFactory($factory);
	}
}
