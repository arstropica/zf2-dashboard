<?php
namespace TenStreet\Controller\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TenStreet\Controller\SoapClientController;

class SoapClientControllerFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $service)
	{
		$parentService = $service->getServiceLocator();
		return new SoapClientController($parentService);
	}
}