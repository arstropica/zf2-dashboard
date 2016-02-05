<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/TenStreet for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace TenStreet;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use TenStreet\Hydrator\TenStreetHydrator;
use TenStreet\Mapper\TenStreetDataMapper;
use TenStreet\Hydrator\Strategy\PhoneNumberStrategy;
use TenStreet\Hydrator\Strategy\MapperNamingStrategy;
use TenStreet\Event\Listener\PostClientDataListener;

class Module implements AutoloaderProviderInterface {
	
	public function getAutoloaderConfig() {
		return array (
				'Zend\Loader\ClassMapAutoloader' => array (
						__DIR__ . '/autoload_classmap.php' 
				),
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => array (
								// if we're in a namespace deeper than one level
								// we need to fix the \ in the path
								__NAMESPACE__ => __DIR__ . '/src/' . str_replace( '\\', '/', __NAMESPACE__ ) 
						) 
				) 
		);
	}
	
	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}
	
	public function onBootstrap(MvcEvent $e) {
		// You may not need to do this if you're doing it elsewhere in your
		// application
		$eventManager = $e->getApplication()
			->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach( $eventManager );
		$sharedEvents = $e->getApplication()
			->getEventManager()
			->getSharedManager();
		$sharedEvents->attach( __NAMESPACE__, 'dispatch', function ($e) {
			$result = $e->getResult();
			$result->setTerminal( true );
		} );
		
		$sm = $e->getApplication()
			->getServiceManager();
		$logger = $sm->get( 'Logger' );
		$eventManager->attach( MvcEvent::EVENT_RENDER_ERROR, function (MvcEvent $e) use($logger) {
			$logger->info( 'An Exception has occurred. ' . $e->getResult()->exception->getMessage() );
		}, - 200 );
		
		$eventManager->attach( new PostClientDataListener( $sm ) );
	}
	
	public function getControllerConfig() {
		return array (
				'factories' => array (
						'TenStreet\Controller\SoapClient' => 'TenStreet\Controller\Factory\SoapClientControllerFactory' 
				) 
		);
	}
	
	public function getServiceConfig() {
		return array (
				'factories' => array (
						'OAuth2Server' => function ($sm) {
							return $sm->get( 'OAuth2Factory' );
						},
						'OAuth2Factory' => function ($sm) {
							$oauth2Factory = $sm->get( 'ZF\OAuth2\Service\OAuth2Server' );
							$sm->setFactory( 'OAuth2FactoryInstance', $oauth2Factory );
							return $sm->get( 'OAuth2FactoryInstance' );
						},
						'TenStreet\Hydrator\PersonNameHydrator' => function ($sm) {
							return new TenStreetHydrator( false );
						},
						'TenStreet\Hydrator\PostalAddressHydrator' => function ($sm) {
							return new TenStreetHydrator( false );
						},
						'TenStreet\Hydrator\DisplayFieldHydrator' => function ($sm) {
							return new TenStreetHydrator( false );
						},
						'TenStreet\Hydrator\ContactDataHydrator' => function ($sm) {
							$hydrator = new TenStreetHydrator( false );
							$hydrator->addStrategy( 'PrimaryPhone', new PhoneNumberStrategy() );
							$hydrator->setNamingStrategy( new MapperNamingStrategy( array (
									'attributes' => '@attributes' 
							) ) );
							
							return $hydrator;
						},
						'TenStreet\Hydrator\AuthenticationHydrator' => function ($sm) {
							return new TenStreetHydrator( false );
						},
						'TenStreetDataMapper' => function ($sm) {
							$dbAdapter = $sm->get( 'Zend\Db\Adapter\Adapter' );
							return new TenStreetDataMapper( $sm, $dbAdapter );
						},
						'Logger' => function ($sm) {
							$config = $sm->get( 'config' );
							$logger = new \Zend\Log\Logger();
							if (isset( $config ['log'] ['file'] ) && is_writable( dirname( $config ['log'] ['file'] ) )) {
								$writer = new \Zend\Log\Writer\Stream( $config ['log'] ['file'] );
								$logger->addWriter( $writer );
							}
							return $logger;
						} 
				) 
		);
	}
}
