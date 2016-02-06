<?php

namespace WebWorks\Hydrator\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use WebWorks\Hydrator\WebWorksHydrator;
use WebWorks\Hydrator\Strategy\DisplayFieldHydratorStrategy;

class ApplicationDataHydratorFactory implements FactoryInterface {

	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$hydrator = new WebWorksHydrator(false);
		
		$parentlocator = $serviceLocator->getServiceLocator();
		
		$hydrator->addStrategy('DisplayFields', new DisplayFieldHydratorStrategy($parentlocator->get('WebWorks\Hydrator\DisplayFieldHydrator')));
		
		return $hydrator;
	}
}