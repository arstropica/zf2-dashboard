<?php

namespace TenStreet\Hydrator\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TenStreet\Hydrator\TenStreetHydrator;
use TenStreet\Hydrator\Strategy\DisplayFieldHydratorStrategy;

class ApplicationDataHydratorFactory implements FactoryInterface {
	
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$hydrator = new TenStreetHydrator( false );
		
		$parentlocator = $serviceLocator->getServiceLocator();
		
		$hydrator->addStrategy( 'DisplayFields', new DisplayFieldHydratorStrategy( $parentlocator->get( 'TenStreet\Hydrator\DisplayFieldHydrator' ) ) );
		
		return $hydrator;
	}
}