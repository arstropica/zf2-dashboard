<?php
namespace Application\Service\Factory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Application\Service\LeadService;

/**
 *
 * @author arstropica
 *        
 */
class LeadServiceFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		$service = new LeadService();
		$service->setEntityManager(
				$serviceLocator->get('Doctrine\ORM\EntityManager'));
		return $service;
	}
}

?>