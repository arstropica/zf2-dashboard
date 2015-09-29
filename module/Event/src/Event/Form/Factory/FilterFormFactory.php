<?php
namespace Event\Form\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Event\Form\FilterForm;

/**
 *
 * @author arstropica
 *        
 */
class FilterFormFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		$services = $serviceLocator->getServiceLocator();
		$entityManager = $services->get('Doctrine\ORM\EntityManager');
		
		$form = new FilterForm($entityManager);
		return $form;
	}
}
