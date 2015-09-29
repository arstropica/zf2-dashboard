<?php
namespace Email\Event\Listener;
use Zend\EventManager\ListenerAggregateInterface;
use Application\Event\Listener\AggregateAbstractListener as BaseListener;
use Application\Event\ServiceEvent;
use Zend\EventManager\EventManagerInterface;

/**
 *
 * @author arstropica
 *        
 */
class SendMailListener extends BaseListener implements 
		ListenerAggregateInterface
{

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Application\Event\Listener\AggregateAbstractListener::attach()
	 */
	public function attach (EventManagerInterface $events)
	{
		$sharedManager = $events->getSharedManager();
		
		$this->_attachStateful($sharedManager, 'Email\Service\SendMailService', 
				'SendMail');
		parent::attach($events);
	}

	public function postSendMail (ServiceEvent $e)
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
					$data['addressTo'] = $e->getParam('addressTo', 
							'Unknown Recipient');
					$data['outcome'] = $e->getOutcome();
					
					foreach ([
							'\Event\Entity\EmailApiEvent',
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

?>