<?php
namespace Lead\Form\Factory\Attribute;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @author arstropica
 *        
 */
class AddFormFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		return $serviceLocator->get('FormElementManager')->get('Lead\Form\Attribute\AddForm');
	}
}
