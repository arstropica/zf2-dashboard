<?php

namespace Report\Entity\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Report\Entity\Report;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Application\Provider\ObjectManagerAwareTrait;
use Application\Provider\FlashMessengerAwareTrait;
use Application\Provider\ElasticaAwareTrait;
use Report\Provider\ResultAwareTrait;

/**
 * Event Listener for Result Generation
 *
 * @author arstropica
 *        
 */
class ReportListener implements ServiceLocatorAwareInterface, ObjectManagerAwareInterface {
	
	use ServiceLocatorAwareTrait, ObjectManagerAwareTrait, FlashMessengerAwareTrait, ElasticaAwareTrait, ResultAwareTrait;

	/**
	 * Initialize with ServiceLocator
	 *
	 * @param ServiceLocatorAwareInterface $serviceLocator        	
	 */
	public function __construct(ServiceLocatorInterface $serviceLocator = null)
	{
		if ($serviceLocator) {
			$this->setServiceLocator($serviceLocator);
		}
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
		$report = $oArgs->getEntity();
		if ($report instanceof Report) {
			$data = $this->generateResults($report, 0, '_score', 'desc', true);
			if ($data && $data->count()) {
				$report->addResults($data);
			}
		}
	}
}

?>