<?php
namespace TenStreet\Controller\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TenStreet\Controller\SoapClientController;
use OAuth2\Server as OAuth2Server;

class SoapClientControllerFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $service)
	{
		$parentService = $service->getServiceLocator();
		$oauth2Server = $parentService->get('OAuth2Server');
		
		if ($oauth2Server instanceof OAuth2Server) {
			return new SoapClientController($oauth2Server, $parentService);
		}
		
		return false;
	}
}