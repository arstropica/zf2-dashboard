<?php
namespace Event\Entity;
use Doctrine\ORM\Mapping as ORM;
use Event\Entity\Event;
use Lead\Entity\Lead;

/**
 * AccountEvent
 *
 * @ORM\Table(name="events_lead")
 * @ORM\Entity(repositoryClass="Event\Entity\Repository\LeadEventRepository")
 */
class LeadEvent
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
	 * @var \Lead\Entity\Lead @ORM\ManyToOne(
	 *      targetEntity="Lead\Entity\Lead",
	 *      inversedBy="events",
	 *      fetch="EXTRA_LAZY",
	 *      cascade={"merge", "persist"},
	 *      )
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(
	 *      name="lead_id",
	 *      referencedColumnName="id",
	 *      nullable=false,
	 *      )
	 *      })
	 */
	private $lead;

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
	 * Set lead
	 *
	 * @param \Lead\Entity\Lead $lead        	
	 *
	 * @return LeadEvent
	 */
	public function setLead (\Lead\Entity\Lead $lead = null)
	{
		$this->lead = $lead;
		
		return $this;
	}

	/**
	 * Get lead
	 *
	 * @return \Lead\Entity\Lead
	 */
	public function getLead ()
	{
		return $this->lead;
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
