<?php

namespace Application\Event\Listener;

use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 *
 * Event Listener for Doctrine Cache Expiration
 *
 * @author arstropica
 *        
 */

class EntityCacheAwareListener implements ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait;

	/**
	 * Initialize with ServiceLocator
	 *
	 * @param ServiceLocatorAwareInterface $serviceLocator        	
	 */
	public function __construct(ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
	}

	/**
	 * Occurs after Entity persistence
	 *
	 * @ORM\PostPersist
	 *
	 * @param LifecycleEventArgs $oArgs        	
	 */
	public function postPersist(LifecycleEventArgs $oArgs)
	{
		try {
			$oEntity = $oArgs->getEntity();
			
			$entityName = is_object($oEntity) ? basename(str_replace('\\', '/', get_class($oEntity))) : false;
			
			switch ($entityName) {
				case 'Lead' :
				case 'LeadAttribute' :
				case 'LeadAttributeValue' :
				case 'Account' :
					$this->_deleteCache($oArgs);
					break;
			}
		} catch ( \Exception $e ) {
		}
	}

	/**
	 * Occurs after Entity updates
	 *
	 * @ORM\PostUpdate
	 *
	 * @param LifecycleEventArgs $oArgs        	
	 */
	public function postUpdate(LifecycleEventArgs $oArgs)
	{
		try {
			$oEntity = $oArgs->getEntity();
			
			$entityName = is_object($oEntity) ? basename(str_replace('\\', '/', get_class($oEntity))) : false;
			
			switch ($entityName) {
				case 'Lead' :
				case 'LeadAttribute' :
				case 'LeadAttributeValue' :
				case 'Account' :
					$this->_deleteCache($oArgs);
					break;
			}
		} catch ( \Exception $e ) {
		}
	}

	/**
	 * Occurs before Entity Removal
	 *
	 * @ORM\PreRemove
	 *
	 * @param LifecycleEventArgs $oArgs        	
	 */
	public function preRemove(LifecycleEventArgs $oArgs)
	{
		try {
			$oEntity = $oArgs->getEntity();
			
			$entityName = is_object($oEntity) ? basename(str_replace('\\', '/', get_class($oEntity))) : false;
			
			switch ($entityName) {
				case 'Lead' :
				case 'LeadAttribute' :
				case 'LeadAttributeValue' :
				case 'Account' :
					$this->_deleteCache($oArgs);
					break;
			}
		} catch ( \Exception $e ) {
		}
	}

	private function _deleteCache(LifecycleEventArgs $oArgs)
	{
		$em = $oArgs->getEntityManager();
		// Get an instance of the configuration
		$config = $em->getConfiguration();
		
		// Gets Query Cache Driver
		$queryCacheDriver = $config->getQueryCacheImpl();
		$queryCacheDriver->deleteAll();
		
		// Gets Result Cache Driver
		$resultCacheDriver = $config->getResultCacheImpl();
		$resultCacheDriver->deleteAll();
	}
}

?>