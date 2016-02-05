<?php

namespace TenStreet\Hydrator\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TenStreet\Hydrator\TenStreetHydrator;
use TenStreet\Hydrator\Strategy\SubEntityHydratorStrategy;

class TenStreetDataHydratorFactory implements FactoryInterface {
	
	public function createService(ServiceLocatorInterface $serviceLocator) {
		$hydrator = new TenStreetHydrator( false );
		
		$parentlocator = $serviceLocator->getServiceLocator();
		
		$hydrator->addStrategy( 'Authentication', new SubEntityHydratorStrategy( $parentlocator->get( 'TenStreet\Hydrator\AuthenticationHydrator' ) ) );
		
		$hydrator->addStrategy( 'ApplicationData', new SubEntityHydratorStrategy( $serviceLocator->get( 'TenStreet\Hydrator\ApplicationDataHydrator' ) ) );
		
		$hydrator->addStrategy( 'PersonalData', new SubEntityHydratorStrategy( $serviceLocator->get( 'TenStreet\Hydrator\PersonalDataHydrator' ) ) );
		
		return $hydrator;
	}
}