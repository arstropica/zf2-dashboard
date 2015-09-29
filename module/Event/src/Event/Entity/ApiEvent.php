<?php
namespace Event\Entity;
use Doctrine\ORM\Mapping as ORM;
use Event\Entity\Event;

/**
 * ApiEvent
 *
 * @ORM\Table(name="events_api")
 * @ORM\Entity
 */
class ApiEvent
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
	 * @var \Api\Entity\Api @ORM\ManyToOne(
	 *      targetEntity="Api\Entity\Api",
	 *      fetch="EXTRA_LAZY",
	 *      cascade={"merge", "persist"},
	 *      )
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(
	 *      name="api_id",
	 *      referencedColumnName="id",
	 *      nullable=false,
	 *      )
	 *      })
	 */
	private $api;

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
	 * Set api
	 *
	 * @param \Api\Entity\Api $api        	
	 *
	 * @return ApiEvent
	 */
	public function setApi (\Api\Entity\Api $api = null)
	{
		$this->api = $api;
		
		return $this;
	}

	/**
	 * Get api
	 *
	 * @return \Api\Entity\Api
	 */
	public function getApi ()
	{
		return $this->api;
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
	 * @return AccountApiEvent
	 */
	public function setId ($id)
	{
		$this->id = $id;
		
		return $this;
	}
}
