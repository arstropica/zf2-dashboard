<?php
namespace Event\Entity;
use Doctrine\ORM\Mapping as ORM;
use Event\Entity\Event;

/**
 * ErrorEvent
 *
 * @ORM\Table(name="events_error")
 * @ORM\Entity
 */
class ErrorEvent
{

	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;

	/**
	 *
	 * @var string @ORM\Column(name="trace", type="text", length=65535,
	 *      nullable=true)
	 */
	private $trace;

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
	 * Set trace
	 *
	 * @param string $trace        	
	 *
	 * @return Event
	 */
	public function setTrace ($trace)
	{
		$this->trace = $trace;
		
		return $this;
	}

	/**
	 * Get trace
	 *
	 * @return string
	 */
	public function getTrace ()
	{
		return $this->trace;
		
		return $this;
	}

	/**
	 *
	 * @return \Event\Entity\Event
	 */
	public function getEvent ()
	{
		return $this->event;
	}

	/**
	 *
	 * @param \Event\Entity\Event $event        	
	 */
	public function setEvent ($event)
	{
		$this->event = $event;
		
		return $this;
	}

	/**
	 *
	 * @return int
	 */
	public function getId ()
	{
		return $this->id;
	}

	/**
	 *
	 * @param int $id        	
	 *
	 * @return ErrorEvent
	 */
	public function setId ($id)
	{
		$this->id = $id;
		
		return $this;
	}
}
