<?php
namespace Event\Entity;
use Doctrine\ORM\Mapping as ORM;
use Event\Entity\Event;

/**
 * EmailApiEvent
 *
 * @ORM\Table(name="events_api_email")
 * @ORM\Entity
 */
class EmailApiEvent
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
	 * @var \Account\Entity\Account @ORM\ManyToOne(
	 *      targetEntity="Account\Entity\Account",
	 *      inversedBy="events",
	 *      fetch="EXTRA_LAZY",
	 *      cascade={"merge", "persist"},
	 *      )
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="account_id", referencedColumnName="id")
	 *      })
	 */
	private $account;

	/**
	 *
	 * @var string @ORM\Column(name="address_to", type="string", length=255,
	 *      nullable=false)
	 */
	private $addressTo;

	/**
	 *
	 * @var boolean @ORM\Column(name="outcome", type="boolean", nullable=false)
	 */
	private $outcome;

	/**
	 *
	 * @var string @ORM\Column(name="response", type="text", length=65535,
	 *      nullable=true)
	 */
	private $response;

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
	 * Set account
	 *
	 * @param \Account\Entity\Account $account        	
	 *
	 * @return EventsApiAccount
	 */
	public function setAccount (\Account\Entity\Account $account = null)
	{
		$this->account = $account;
		
		return $this;
	}

	/**
	 * Get account
	 *
	 * @return \Account\Entity\Account
	 */
	public function getAccount ()
	{
		return $this->account;
	}

	/**
	 * Set addressTo
	 *
	 * @param string $addressTo        	
	 *
	 * @return EmailApiEvent
	 */
	public function setAddressTo ($addressTo)
	{
		$this->addressTo = $addressTo;
		
		return $this;
	}

	/**
	 * Get addressTo
	 *
	 * @return string
	 */
	public function getAddressTo ()
	{
		return $this->addressTo;
	}

	/**
	 * Set outcome
	 *
	 * @param boolean $outcome        	
	 *
	 * @return EmailApiEvent
	 */
	public function setOutcome ($outcome)
	{
		$this->outcome = $outcome;
		
		return $this;
	}

	/**
	 * Get outcome
	 *
	 * @return boolean
	 */
	public function getOutcome ()
	{
		return $this->outcome;
	}

	/**
	 * Set response
	 *
	 * @param string $response        	
	 *
	 * @return EmailApiEvent
	 */
	public function setResponse ($response)
	{
		$this->response = $response;
		
		return $this;
	}

	/**
	 * Get response
	 *
	 * @return string
	 */
	public function getResponse ()
	{
		return $this->response;
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
