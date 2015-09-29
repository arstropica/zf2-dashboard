<?php
namespace Application\Controller\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Controller\Plugin\JSONErrorResponse;

/**
 *
 * @author arstropica
 *        
 */
class JsonErrorResponseFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $pluginManager)
	{
		$serviceManager = $pluginManager->getServiceLocator();
		
		return new JSONErrorResponse($serviceManager);
		// return
	// $serviceManager->get('Application\Controller\Plugin\JSONErrorResponse');
	}
}
