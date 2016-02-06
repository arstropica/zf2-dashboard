<?php

namespace Event\Entity;

use Doctrine\ORM\Mapping as ORM;
use Event\Entity\Event;
use Agent\Entity\Agent;

/**
 * AgentEvent
 *
 * @ORM\Table(name="events_agent")
 * @ORM\Entity
 */
class AgentEvent {
	
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;
	
	/**
	 *
	 * @var \Agent\Entity\Agent @ORM\ManyToOne(
	 *      targetEntity="Agent\Entity\Agent",
	 *      inversedBy="events",
	 *      fetch="EXTRA_LAZY",
	 *      cascade={"merge", "persist"},
	 *      )
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(
	 *      name="agent_id",
	 *      referencedColumnName="id",
	 *      nullable=false,
	 *      )
	 *      })
	 */
	private $agent;
	
	/**
	 * @ORM\ManyToOne(
	 * targetEntity="Event\Entity\Event",
	 * fetch="EXTRA_LAZY",
	 * cascade={"merge", "persist"},
	 * )
	 * @ORM\JoinColumn(
	 * name="event_id",
	 * referencedColumnName="id",
	 * nullable=false,
	 * )
	 */
	private $event;

	/**
	 * Set agent
	 *
	 * @param \Agent\Entity\Agent $agent        	
	 *
	 * @return AgentEvent
	 */
	public function setAgent(\Agent\Entity\Agent $agent = null)
	{
		$this->agent = $agent;
		
		return $this;
	}

	/**
	 * Get agent
	 *
	 * @return \Agent\Entity\Agent
	 */
	public function getAgent()
	{
		return $this->agent;
	}

	/**
	 *
	 * @return \Event\Entity\Event
	 */
	public function getEvent()
	{
		return $this->event;
	}

	/**
	 *
	 * @param \Event\Entity\Event $event        	
	 */
	public function setEvent($event)
	{
		$this->event = $event;
		
		return $this;
	}

	/**
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @param int $id        	
	 *
	 * @return AgentEvent
	 */
	public function setId($id)
	{
		$this->id = $id;
		
		return $this;
	}
}
