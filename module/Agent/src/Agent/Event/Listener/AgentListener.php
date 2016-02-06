<?php

namespace Agent\Event\Listener;

use Application\Event\Listener\AggregateAbstractListener as BaseListener;
use Application\Event\ServiceEvent;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;

/**
 *
 * @author arstropica
 *        
 */
class AgentListener extends BaseListener implements ListenerAggregateInterface {

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\EventManager\ListenerAggregateInterface::attach()
	 */
	public function attach(EventManagerInterface $events)
	{
		$sharedManager = $events->getSharedManager();
		foreach ( [ 
				'Agent' 
		] as $prefix ) {
			foreach ( [ 
					'edit',
					'add',
					'delete' 
			] as $action ) {
				try {
					$this->_attachStateful($sharedManager, "Agent\\Controller\\{$prefix}Controller", ucfirst($action) . 'Action');
				} catch ( \Exception $e ) {
				}
			}
		}
		parent::attach($events);
	}

	public function postEditAction(ServiceEvent $e)
	{
		$this->_editAgent($e);
	}

	public function postAddAction(ServiceEvent $e)
	{
		$this->_editAgent($e);
	}

	public function postDeleteAction(ServiceEvent $e)
	{
		$this->_editAgent($e);
	}

	public function _editAgent(ServiceEvent $e)
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
					
					foreach ( [ 
							'\Event\Entity\AgentEvent' 
					] as $_event ) {
						$event = new $_event();
						$this->dispatch($entity, $event, $data);
					}
				}
			}
		} catch ( \Exception $e ) {
			// fail silently
		}
	}
}

?>