<?php

namespace Report\Form\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @author arstropica
 *        
 */
class AddFormFactory implements FactoryInterface {

	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		return $serviceLocator->get('FormElementManager')
			->get('Report\Form\AddForm');
	}
}
