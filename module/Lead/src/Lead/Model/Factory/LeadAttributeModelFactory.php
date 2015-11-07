<?php
namespace Lead\Model\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Lead\Model\LeadAttributeModel;

/**
 *
 * @author arstropica
 *        
 */
class LeadAttributeModelFactory implements FactoryInterface
{

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\ServiceManager\FactoryInterface::createService()
	 *
	 */
	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		return new LeadAttributeModel($serviceLocator);
	}
}

?>