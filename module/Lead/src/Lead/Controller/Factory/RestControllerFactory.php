<?php
namespace Lead\Controller\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Lead\Controller\RestController;
use OAuth2\Server as OAuth2Server;

class RestControllerFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $service)
	{
		$parentService = $service->getServiceLocator();
		$oauth2Server = $parentService->get('OAuth2Server');
		
		if ($oauth2Server instanceof OAuth2Server) {
			return new RestController($oauth2Server);
		}
		
		return false;
	}
}