<?php

namespace WebWorks\Hydrator\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use WebWorks\Hydrator\WebWorksHydrator;
use WebWorks\Hydrator\Strategy\SubEntityHydratorStrategy;

class WebWorksDataHydratorFactory implements FactoryInterface {

	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$hydrator = new WebWorksHydrator(false);
		
		$parentlocator = $serviceLocator->getServiceLocator();
		
		$hydrator->addStrategy('ApplicationData', new SubEntityHydratorStrategy($serviceLocator->get('WebWorks\Hydrator\ApplicationDataHydrator')));
		
		$hydrator->addStrategy('PersonalData', new SubEntityHydratorStrategy($serviceLocator->get('WebWorks\Hydrator\PersonalDataHydrator')));
		
		return $hydrator;
	}
}