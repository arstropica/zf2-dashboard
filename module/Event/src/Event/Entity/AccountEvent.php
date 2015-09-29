<?php
namespace Event\Entity;
use Doctrine\ORM\Mapping as ORM;
use Event\Entity\Event;

/**
 * AccountEvent
 *
 * @ORM\Table(name="events_account")
 * @ORM\Entity(repositoryClass="Event\Entity\Repository\AccountEventRepository")
 */
class AccountEvent
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
	 *      @ORM\JoinColumn(
	 *      name="account_id",
	 *      referencedColumnName="id",
	 *      nullable=false,
	 *      )
	 *      })
	 */
	private $account;

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
	 * @return AccountEvent
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
