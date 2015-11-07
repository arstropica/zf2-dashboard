<?php
namespace Lead\Model;
use Application\Provider\EntityManagerAwareTrait;
use Application\Provider\EntityClassAwareTrait;
use Application\Provider\EntityStorageAwareTrait;
use Application\Provider\EntitySortableAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @author arstropica
 *        
 */
class LeadAttributeModel
{
	use ServiceLocatorAwareTrait, EntityManagerAwareTrait, EntityClassAwareTrait, EntityStorageAwareTrait, EntitySortableAwareTrait;

	/**
	 * Constructor
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 */
	function __construct (ServiceLocatorInterface $serviceLocator)
	{
		$entityClass = 'Lead\Entity\LeadAttribute';
		$this->setServiceLocator($serviceLocator);
		$this->setEntityClass($entityClass);
	}
}

?>