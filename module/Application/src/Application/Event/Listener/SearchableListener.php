<?php

namespace Application\Event\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use \Application\Service\ElasticSearch\SearchableEntityInterface;
use Application\Provider\SearchManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Mapping as ORM;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Lead\Entity\LeadAttributeValue;
use Lead\Entity\Lead;
use Application\Provider\FlashMessengerAwareTrait;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Event Listener for Entity-Document Serialization
 *
 * @author arstropica
 *        
 */

class SearchableListener implements SearchableEntityInterface {
	use SearchManagerAwareTrait, ServiceLocatorAwareTrait, FlashMessengerAwareTrait;
	
	/**
	 *
	 * @var ArrayCollection
	 */
	private $entities;

	/**
	 * Initialize with ServiceLocator
	 *
	 * @param ServiceLocatorAwareInterface $serviceLocator        	
	 */
	public function __construct(ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
		$this->entities = new ArrayCollection();
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
		if ($oEntity instanceof ServiceLocatorAwareInterface) {
			$oEntity->setServiceLocator($this->getServiceLocator());
		}
		if ($oEntity instanceof LeadAttributeValue) {
			$lead = $oEntity->getLead();
			$_lead = $lead ? $lead->getId() : 0;
			$oEntity->setParent($_lead);
			$oEntity->setDate($oEntity->getValue());
			$oEntity->setNumber($oEntity->getValue());
		}
		if ($oEntity instanceof Lead) {
			$description = $oEntity->getFullName();
			$oEntity->setDescription($description);
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
		if ($oEntity instanceof SearchableEntityInterface) {
			$this->entities->add($oEntity);
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
		$oEntity = $oArgs->getEntity();
		if ($oEntity instanceof SearchableEntityInterface) {
			$this->entities->add($oEntity);
		}
	}

	/**
	 * Occurs at the end of the EventManager flush
	 *
	 * @ORM\PostFlush
	 *
	 * @param PostFlushEventArgs $oArgs        	
	 */
	public function postFlush(PostFlushEventArgs $oArgs)
	{
		try {
			$entities = $this->entities;
			$em = $oArgs->getEntityManager();
			foreach ( $entities as $oEntity ) {
				if ($oEntity->getId() > 0) {
					$oEntity = $em->merge($oEntity);
					if ($oEntity instanceof Lead) {
						$attributes = $oEntity->getAttributes(true);
						foreach ( $attributes as $attribute ) {
							$attribute->setParent($oEntity->getId());
						}
					}
					$this->getSearchManager()
						->persist($oEntity);
				}
			}
			$this->getSearchManager()
				->flush();
			$this->entities = new ArrayCollection();
		} catch ( \Exception $e ) {
			$this->getFlashMessenger()
				->addErrorMessage($e->getMessage());
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
			if ($oEntity instanceof SearchableEntityInterface) {
				$this->getSearchManager()
					->remove($oEntity);
				$this->getSearchManager()
					->flush($oEntity);
			}
		} catch ( \Exception $e ) {
			$this->getFlashMessenger()
				->addErrorMessage($e->getMessage());
		}
	}
}
