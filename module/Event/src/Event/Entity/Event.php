<?php

namespace Event\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Entity
 * @ORM\Table(name="events")
 */
class Event {
	
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;
	
	/**
	 *
	 * @var string @ORM\Column(name="event", type="string", length=45,
	 *      nullable=false)
	 */
	protected $event;
	
	/**
	 *
	 * @var \DateTime @ORM\Column(name="occurred", type="datetime",
	 *      nullable=false)
	 */
	protected $occurred;
	
	/**
	 *
	 * @var string @ORM\Column(name="message", type="text", length=65535,
	 *      nullable=true)
	 */
	protected $message;

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set id
	 *
	 * @param int $id        	
	 *
	 * @return Event
	 */
	public function setId($id)
	{
		$this->id = $id;
		
		return $this;
	}

	/**
	 * Get event
	 *
	 * @return string
	 */
	public function getEvent()
	{
		return $this->event;
	}

	/**
	 * Set event
	 *
	 * @param string $event        	
	 *
	 * @return Event
	 */
	public function setEvent($event)
	{
		$this->event = $event;
		
		return $this;
	}

	/**
	 * Set occurred
	 *
	 * @param \DateTime $occurred        	
	 *
	 * @return Event
	 */
	public function setOccurred($occurred)
	{
		$this->occurred = $occurred;
		
		return $this;
	}

	/**
	 * Get occurred
	 *
	 * @return \DateTime
	 */
	public function getOccurred()
	{
		return $this->occurred;
	}

	/**
	 * Set message
	 *
	 * @param string $message        	
	 *
	 * @return Event
	 */
	public function setMessage($message)
	{
		$this->message = $message;
		
		return $this;
	}

	/**
	 * Get message
	 *
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}
}
