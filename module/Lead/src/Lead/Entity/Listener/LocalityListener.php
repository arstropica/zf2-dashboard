<?php

namespace Lead\Entity\Listener;

use Lead\Entity\Lead;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 *
 * @author arstropica
 *        
 */
class LocalityListener implements ServiceLocatorAwareInterface {
	
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
	 * Occurs after Entity is constructed by the EntityManager.
	 *
	 * @ORM\PostLoad
	 *
	 * @param LifecycleEventArgs $oArgs        	
	 */
	public function postLoad(LifecycleEventArgs $oArgs)
	{
		$oEntity = $oArgs->getEntity();
		if ($oEntity instanceof Lead) {
			$oEntity->setServiceLocator($this->getServiceLocator());
			$oEntity->getLocality();
		}
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
		$oEntity = $oArgs->getEntity();
		if ($oEntity instanceof Lead) {
			$oEntity->setServiceLocator($this->getServiceLocator());
			$oEntity->getLocality();
		}
	}

	/**
	 * Occurs after Entity Update
	 *
	 * @ORM\PostUpdate
	 *
	 * @param LifecycleEventArgs $oArgs        	
	 */
	public function postUpdate(LifecycleEventArgs $oArgs)
	{
		$oEntity = $oArgs->getEntity();
		if ($oEntity instanceof Lead) {
			$oEntity->setServiceLocator($this->getServiceLocator());
			$oEntity->getLocality();
		}
	}
}

?>