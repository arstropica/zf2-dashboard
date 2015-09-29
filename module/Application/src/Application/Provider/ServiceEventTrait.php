<?php
namespace Application\Provider;
use Application\Event\ServiceEvent;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @author arstropica
 *        
 */
trait ServiceEventTrait
{

	/**
	 *
	 * @var ArrayCollection
	 */
	protected $serviceEvents;

	/**
	 *
	 * @var ServiceEvent
	 */
	protected $serviceEvent;

	/**
	 *
	 * @return ServiceEvent
	 */
	public function getServiceEvent ()
	{
		if (! $this->serviceEvents) {
			$serviceEvent = new ServiceEvent();
			$this->setServiceEvent($serviceEvent);
		}
		return $this->serviceEvents->last();
	}

	/**
	 *
	 * @param \Application\Event\ServiceEvent $serviceEvent        	
	 *
	 * @return ServiceEvent
	 */
	public function setServiceEvent ($serviceEvent)
	{
		if (! $this->serviceEvents) {
			$this->serviceEvents = new ArrayCollection();
		}
		$this->serviceEvents->add($serviceEvent);
		
		return $serviceEvent;
	}

	/**
	 *
	 * @param \Application\Event\ServiceEvent $serviceEvent        	
	 *
	 * @return ServiceEvent
	 */
	public function createServiceEvent ()
	{
		$serviceEvent = new ServiceEvent();
		
		$this->setServiceEvent($serviceEvent);
		
		return $serviceEvent;
	}
}

?>