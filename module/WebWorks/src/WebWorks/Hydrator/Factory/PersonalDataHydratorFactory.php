<?php

namespace WebWorks\Hydrator\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use WebWorks\Hydrator\WebWorksHydrator;
use WebWorks\Hydrator\Strategy\SubEntityHydratorStrategy;

class PersonalDataHydratorFactory implements FactoryInterface {

	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$hydrator = new WebWorksHydrator(false);
		
		$parentlocator = $serviceLocator->getServiceLocator();
		
		$hydrator->addStrategy('PersonName', new SubEntityHydratorStrategy($parentlocator->get('WebWorks\Hydrator\PersonNameHydrator')));
		
		$hydrator->addStrategy('PostalAddress', new SubEntityHydratorStrategy($parentlocator->get('WebWorks\Hydrator\PostalAddressHydrator')));
		
		$hydrator->addStrategy('ContactData', new SubEntityHydratorStrategy($parentlocator->get('WebWorks\Hydrator\ContactDataHydrator')));
		
		return $hydrator;
	}
}