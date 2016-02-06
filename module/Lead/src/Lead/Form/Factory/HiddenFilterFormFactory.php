<?php

namespace Lead\Form\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Lead\Form\HiddenFilterForm;

/**
 *
 * @author arstropica
 *        
 */
class HiddenFilterFormFactory implements FactoryInterface {

	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$services = $serviceLocator->getServiceLocator();
		$entityManager = $services->get('Doctrine\ORM\EntityManager');
		
		$form = new HiddenFilterForm();
		return $form;
	}
}
