<?php
namespace Application\Controller\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Controller\Plugin\ErrorResponse;

/**
 *
 * @author arstropica
 *        
 */
class ErrorResponseFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $pluginManager)
	{
		$serviceManager = $pluginManager->getServiceLocator();
		
		return new ErrorResponse($serviceManager);
	}
}
