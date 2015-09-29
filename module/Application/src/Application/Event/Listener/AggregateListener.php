<?php
namespace Application\Event\Listener;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;

/**
 *
 * @author arstropica
 *        
 */
class AggregateListener extends AggregateAbstractListener implements 
		ListenerAggregateInterface
{

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\EventManager\ListenerAggregateInterface::attach()
	 *
	 */
	public function attach (EventManagerInterface $events)
	{
		parent::attach($events);
	}
}

?>