<?php

namespace WebWorks\Controller\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use WebWorks\Controller\XMLClientController;

class XMLClientControllerFactory implements FactoryInterface {

	public function createService(ServiceLocatorInterface $service)
	{
		$parentService = $service->getServiceLocator();
		return new XMLClientController($parentService);
	}
}