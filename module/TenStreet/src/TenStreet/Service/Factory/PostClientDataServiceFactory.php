<?php
namespace TenStreet\Service\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TenStreet\Service\PostClientDataService;

/**
 *
 * @author arstropica
 *        
 */
class PostClientDataServiceFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $service)
	{
		try {
			return new PostClientDataService($service);
		} catch (\Exception $e) {
			return false;
		}
	}
}

?>