<?php

namespace Application\Provider;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Lead\Entity\Lead;
use Application\Service\ElasticSearch\SearchableEntityInterface;

/**
 *
 * @author arstropica
 *        
 */
trait SearchableAwareTrait {

	/**
	 * Occurs after Entity persistence
	 *
	 * @ORM\PostPersist
	 *
	 * @param Lead $lead        	
	 * @param LifecycleEventArgs $oArgs        	
	 */
	public function postPersist(Lead $lead, LifecycleEventArgs $oArgs)
	{
		$oEntity = $oArgs->getEntity();
		/* @var $logger \Zend\Log\Logger */
		$logger = $this->get('logger');
		
		$logger->debug(get_class($this->getSearchManager()));
		if ($oEntity instanceof SearchableEntityInterface) {
			$this->getSearchManager()
				->persist($oEntity);
		}
	}

	/**
	 * Occurs before Entity Removal
	 *
	 * @ORM\PreRemove
	 *
	 * @param Lead $lead        	
	 * @param LifecycleEventArgs $oArgs        	
	 */
	public function preRemove(Lead $lead, LifecycleEventArgs $oArgs)
	{
		$oEntity = $oArgs->getEntity();
		if ($oEntity instanceof SearchableEntityInterface) {
			$this->getSearchManager()
				->remove($oEntity);
		}
	}

	/**
	 * Occurs after Entity Update
	 *
	 * @ORM\PostUpdate
	 *
	 * @param Lead $lead        	
	 * @param LifecycleEventArgs $oArgs        	
	 */
	public function postUpdate(Lead $lead, LifecycleEventArgs $oArgs)
	{
		$oEntity = $oArgs->getEntity();
		
		var_dump($this->getSearchManager());
		exit();
		if ($oEntity instanceof SearchableEntityInterface) {
			$this->getSearchManager()
				->persist($oEntity);
		}
	}
}

?>