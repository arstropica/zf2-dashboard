<?php
namespace Lead\Event\Listener;
use Application\Event\Listener\AggregateAbstractListener as BaseListener;
use Application\Event\ServiceEvent;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;

/**
 *
 * @author arstropica
 *        
 */
class AttributeListener extends BaseListener implements ListenerAggregateInterface
{

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\EventManager\ListenerAggregateInterface::attach()
	 */
	public function attach (EventManagerInterface $events)
	{
		$sharedManager = $events->getSharedManager();
		foreach ([
				'Attribute'
		] as $prefix) {
			foreach ([
					'list',
					'edit',
					'add',
					'delete',
			] as $action) {
				try {
					$this->_attachStateful($sharedManager, 
							"Lead\\Controller\\{$prefix}Controller", 
							ucfirst($action) . 'Action');
				} catch (\Exception $e) {}
			}
		}
		parent::attach($events);
	}

	public function postListAction (ServiceEvent $e)
	{
		$this->_editAttribute($e);
	}

	public function postEditAction (ServiceEvent $e)
	{
		$this->_editAttribute($e);
	}

	public function postAddAction (ServiceEvent $e)
	{
		$this->_editAttribute($e);
	}

	public function postDeleteAction (ServiceEvent $e)
	{
		$this->_editAttribute($e);
	}

	public function _editAttribute (ServiceEvent $e)
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
					$data['message'] = $e->getParam('message', $e->getMessage());
					$this->dispatch($entity, null, $data);
				}
			}
		} catch (\Exception $e) {
			// fail silently
		}
	}
}

