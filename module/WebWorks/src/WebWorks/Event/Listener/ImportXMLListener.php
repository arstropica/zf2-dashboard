<?php

namespace WebWorks\Event\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Application\Event\Listener\AggregateAbstractListener as BaseListener;
use Application\Event\ServiceEvent;

/**
 *
 * @author arstropica
 *        
 */
class ImportXMLListener extends BaseListener implements ListenerAggregateInterface {

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\EventManager\ListenerAggregateInterface::attach()
	 */
	public function attach(EventManagerInterface $events)
	{
		$sharedManager = $events->getSharedManager();
		
		$this->_attachStateful($sharedManager, 'WebWorks\Service\ImportXMLService', 'ImportXML');
		parent::attach($events);
	}

	/**
	 *
	 * @param ServiceEvent $e        	
	 */
	public function postImportXML(ServiceEvent $e)
	{
		$this->clearEvent();
		
		if ($e->getIsError()) {
			return $this->OnError($e);
		}
		$data = [ ];
		$entity = false;
		$id = $e->getEntityId();
		
		$em = $this->getEntityManager();
		
		try {
			$r = $em->getRepository($e->getEntityClass());
			if ($r) {
				$entity = $r->findOneBy([ 
						'id' => $id 
				]);
				
				if ($entity) {
					$data ['action'] = $e->getDescription();
					$data ['message'] = $e->getMessage();
					$data ['response'] = $e->getResult();
					$data ['clientId'] = $e->getParam('clientId', 0);
					$data ['outcome'] = $e->getOutcome();
					
					foreach ( [ 
							'\Event\Entity\WebWorksApiEvent' => true,
							'\Event\Entity\LeadEvent' => false 
					] as $_event => $_update ) {
						$event = new $_event();
						$this->dispatch($entity, $event, $data, $_update);
					}
				}
			}
		} catch ( \Exception $e ) {
			// fail silently
		}
	}
}
