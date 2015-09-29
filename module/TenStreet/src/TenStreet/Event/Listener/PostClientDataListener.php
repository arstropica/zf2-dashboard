<?php
namespace TenStreet\Event\Listener;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Application\Event\Listener\AggregateAbstractListener as BaseListener;
use Application\Event\ServiceEvent;

/**
 *
 * @author arstropica
 *        
 */
class PostClientDataListener extends BaseListener implements 
		ListenerAggregateInterface
{

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\EventManager\ListenerAggregateInterface::attach()
	 */
	public function attach (EventManagerInterface $events)
	{
		$sharedManager = $events->getSharedManager();
		
		$this->_attachStateful($sharedManager, 
				'TenStreet\Service\PostClientDataService', 'PostClientData');
		parent::attach($events);
	}

	/**
	 *
	 * @param ServiceEvent $e        	
	 */
	public function postPostClientData (ServiceEvent $e)
	{
		$this->clearEvent();
		
		if ($e->getIsError()) {
			return $this->OnError($e);
		}
		$data = [];
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
					$data['action'] = $e->getDescription();
					$data['message'] = $e->getMessage();
					$data['response'] = $e->getResult();
					$data['service'] = $e->getParam('service', 'subject_upload');
					$data['clientId'] = $e->getParam('clientId', 0);
					$data['outcome'] = $e->getOutcome();
					
					foreach ([
							'\Event\Entity\TenStreetApiEvent',
							'\Event\Entity\LeadEvent'
					] as $_event) {
						$event = new $_event();
						$this->dispatch($entity, $event, $data);
					}
				}
			}
		} catch (\Exception $e) {
			// fail silently
		}
	}
}
