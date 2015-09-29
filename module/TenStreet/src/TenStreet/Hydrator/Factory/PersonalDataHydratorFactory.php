<?php
namespace TenStreet\Hydrator\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TenStreet\Hydrator\TenStreetHydrator;
use TenStreet\Hydrator\Strategy\SubEntityHydratorStrategy;

class PersonalDataHydratorFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		$hydrator = new TenStreetHydrator(false);
		
		$parentlocator = $serviceLocator->getServiceLocator();
		
		$hydrator->addStrategy('PersonName', 
				new SubEntityHydratorStrategy(
						$parentlocator->get('PersonNameHydrator')));
		
		$hydrator->addStrategy('PostalAddress', 
				new SubEntityHydratorStrategy(
						$parentlocator->get('PostalAddressHydrator')));
		
		$hydrator->addStrategy('ContactData', 
				new SubEntityHydratorStrategy(
						$parentlocator->get('ContactDataHydrator')));
		
		return $hydrator;
	}
}