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
use Application\Event\Listener\SearchableListener;
use Doctrine\ORM\Events;
use Lead\Entity\Listener\LocalityListener;
use Application\Event\Listener\EntityCacheAwareListener;

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
		
		$sm = $e->getApplication()
			->getServiceManager();
		
		// Register Event Listeners
		$eventManager->attach(new LeadListener($sm));
		$eventManager->attach(new AttributeListener($sm));
		
		$listener = new SearchableListener($sm);
		
		/* @var $doctrine_em \Doctrine\ORM\EntityManager */
		$doctrine_em = $sm->get('Doctrine\ORM\EntityManager');
		$doctrine_evm = $doctrine_em->getEventManager();
		
		$locality = new LocalityListener($sm);
		$doctrine_evm->addEventListener([ 
				Events::postLoad,
				Events::postUpdate,
				Events::postPersist 
		], $locality);
		
		$doctrine_evm->addEventListener([ 
				Events::postLoad,
				Events::postUpdate,
				Events::postPersist,
				Events::postFlush,
				Events::preRemove 
		], $listener);
		
		$cache = new EntityCacheAwareListener($sm);
		$doctrine_evm->addEventListener([ 
				Events::postPersist,
				Events::postUpdate,
				Events::preRemove 
		], $cache);
	
	}
}
