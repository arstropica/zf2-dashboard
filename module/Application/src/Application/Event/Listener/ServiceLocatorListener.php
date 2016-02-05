<?php

namespace Application\Event\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\Mapping as ORM;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Lead\Entity\Lead;
use Account\Entity\Account;

/**
 * Event Listener for Entity ServiceLocator Injection
 *
 * @author arstropica
 *        
 */
class ServiceLocatorListener implements ServiceLocatorAwareInterface {
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
		if ($oEntity instanceof ServiceLocatorAwareInterface) {
			$oEntity->setServiceLocator($this->getServiceLocator());
		}
	}

	/**
	 * Occurs before the respective EntityManager persist operation for an
	 * entity is executed.
	 *
	 * @ORM\PrePersist
	 *
	 * @param LifecycleEventArgs $oArgs        	
	 */
	public function prePersist(LifecycleEventArgs $oArgs)
	{
		$oEntity = $oArgs->getEntity();
		if ($oEntity instanceof ServiceLocatorAwareInterface) {
			$oEntity->setServiceLocator($this->getServiceLocator());
			
			if ($oEntity instanceof Lead) {
				$events = [ ];
				$account = $oEntity->getAccount();
				if ($account && $account instanceof Account && $oEntity->getId()) {
					/* @var $em \Doctrine\ORM\EntityManager */
					$em = $this->getServiceLocator()
						->get('doctrine.entitymanager.orm_default');
					
					$qb = $em->createQueryBuilder();
					
					$qb->select('e', 'ee', 'we', 'te')
						->from('Event\Entity\Event', 'e')
						->innerJoin('Event\Entity\LeadEvent', 'le', 'WITH', 'le.event = e')
						->leftJoin('Event\Entity\EmailApiEvent', 'ee', 'WITH', 'e = ee.event')
						->leftJoin('Event\Entity\TenStreetApiEvent', 'te', 'WITH', 'e = te.event')
						->leftJoin('Event\Entity\WebWorksApiEvent', 'we', 'WITH', 'e = we.event')
						->where('le.lead = :id')
						->andWhere($qb->expr()
						->orX($qb->expr()
						->isNotNull('te.id'), $qb->expr()
						->isNotNull('ee.id'), $qb->expr()
						->isNotNull('we.id')))
						->orderBy('e.occurred', 'desc')
						->setParameter('id', $oEntity->getId());
					
					$events = $qb->getQuery()
						->getArrayResult();
				}
				$event = array_values(array_filter($events, function ($f) {
					return isset($f ['occurred']);
				}));
				if ($event) {
					$oEntity->setLastsubmitted($event [0] ['occurred']);
				}
			}
		}
	}
}
