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
				case 'Event' :
				case 'Agent' :
				case 'Report' :
				case 'Lead' :
				case 'LeadAttribute' :
				case 'LeadAttributeValue' :
				case 'Account' :
					$prefix = strtolower($entityName) . "-";
					$this->_deleteCache($oArgs, $prefix);
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
				case 'Event' :
				case 'Agent' :
				case 'Report' :
				case 'Lead' :
				case 'LeadAttribute' :
				case 'LeadAttributeValue' :
				case 'Account' :
					$prefix = strtolower($entityName) . "-";
					$this->_deleteCache($oArgs, $prefix);
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
				case 'Event' :
				case 'Agent' :
				case 'Report' :
				case 'Lead' :
				case 'LeadAttribute' :
				case 'LeadAttributeValue' :
				case 'Account' :
					$prefix = strtolower($entityName) . "-";
					$this->_deleteCache($oArgs, $prefix);
					break;
			}
		} catch ( \Exception $e ) {
		}
	}

	/**
	 * Delete Cache Entry
	 *
	 * @param LifecycleEventArgs $oArgs        	
	 * @param string $prefix        	
	 *
	 * @return void
	 */
	private function _deleteCache(LifecycleEventArgs $oArgs, $prefix = null)
	{
		$result = [ ];
		$em = $oArgs->getEntityManager();
		// Get an instance of the configuration
		$config = $em->getConfiguration();
		
		// Gets Query Cache Driver
		/* @var $queryCacheDriver \Application\ORM\Tools\Cache\RedisCache */
		$queryCacheDriver = $config->getQueryCacheImpl();
		
		// Gets Result Cache Driver
		/* @var $resultCacheDriver \Application\ORM\Tools\Cache\RedisCache */
		$resultCacheDriver = $config->getResultCacheImpl();
		
		if ($prefix) {
			$result [] = $queryCacheDriver->deleteByPrefix($prefix);
			$result [] = $resultCacheDriver->deleteByPrefix($prefix);
		} else {
			$result [] = $queryCacheDriver->deleteAll();
			$result [] = $resultCacheDriver->deleteAll();
		}
		echo "<script>console.dir(" . json_encode($result) . ");</script>";
		return $result;
	}
}

?>