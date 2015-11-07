<?php
namespace Lead\Form\Factory\Attribute;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Lead\Form\Attribute\FilterForm;

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
