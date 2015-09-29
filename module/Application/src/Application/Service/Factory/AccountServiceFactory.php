<?php
namespace Application\Service\Factory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Application\Service\AccountService;

/**
 *
 * @author arstropica
 *        
 */
class AccountServiceFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		$service = new AccountService();
		$service->setEntityManager(
				$serviceLocator->get('Doctrine\ORM\EntityManager'));
		return $service;
	}
}

?>