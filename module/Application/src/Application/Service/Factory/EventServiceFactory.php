<?php
namespace Application\Service\Factory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Application\Service\EventService;

/**
 *
 * @author arstropica
 *        
 */
class EventServiceFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		$service = new EventService();
		$service->setEntityManager(
				$serviceLocator->get('Doctrine\ORM\EntityManager'));
		return $service;
	}
}

?>