<?php
namespace Api\Form\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @author arstropica
 *        
 */
class EditFormFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		return $serviceLocator->get('FormElementManager')
			->get('Api\Form\EditForm');
	}
}
