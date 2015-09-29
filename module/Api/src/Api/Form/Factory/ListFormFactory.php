<?php
namespace Api\Form\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Api\Form\ListForm;

/**
 *
 * @author arstropica
 *        
 */
class ListFormFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		$services = $serviceLocator->getServiceLocator();
		$entityManager = $services->get('Doctrine\ORM\EntityManager');
		
		$form = new ListForm($entityManager);
		return $form;
	}
}
