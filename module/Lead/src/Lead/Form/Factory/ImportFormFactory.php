<?php
namespace Lead\Form\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Lead\Form\ImportForm;

/**
 *
 * @author arstropica
 *        
 */
class ImportFormFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		$services = $serviceLocator->getServiceLocator();
		$entityManager = $services->get('Doctrine\ORM\EntityManager');
		
		$form = new ImportForm($entityManager);
		return $form;
	}
}
