<?php
namespace Lead\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;

/**
 * Lead
 *
 * @ORM\Table(name="lead", indexes={@ORM\Index(name="id", columns={"id"})})
 * @ORM\Entity(repositoryClass="Lead\Entity\Repository\LeadRepository")
 * @Annotation\Instance("\Lead\Entity\Lead")
 */
class Lead
{

	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 *      @Annotation\Exclude()
	 */
	private $id;

	/**
	 *
	 * @var \DateTime @ORM\Column(name="timecreated", type="datetime",
	 *      nullable=false)
	 *      @Annotation\Exclude()
	 */
	private $timecreated;

	/**
	 *
	 * @var string @ORM\Column(name="referrer", type="string", length=255,
	 *      nullable=false)
	 *      @Annotation\Exclude()
	 */
	private $referrer;

	/**
	 *
	 * @var string @ORM\Column(name="ipaddress", type="string", length=15,
	 *      nullable=false)
	 *      @Annotation\Exclude()
	 */
	private $ipaddress;

	/**
	 *
	 * @var string @Annotation\Exclude()
	 */
	private $description;

	/**
	 * @ORM\ManyToOne(
	 * targetEntity="Account\Entity\Account",
	 * inversedBy="leads",
	 * cascade={"persist"},
	 * fetch="EXTRA_LAZY",
	 * )
	 * @ORM\JoinColumn(
	 * name="account_id",
	 * referencedColumnName="id",
	 * nullable=true,
	 * )
	 * @Annotation\Instance("\Account\Entity\Account")
	 * @Annotation\Type("DoctrineModule\Form\Element\ObjectSelect")
	 * @Annotation\Filter({"name":"StripTags"})
	 * @Annotation\Filter({"name":"StringTrim"})
	 * @Annotation\Validator({"name":"Digits"})
	 * @Annotation\Required(false)
	 * @Annotation\Options({
	 * "required":"false",
	 * "label":"Account",
	 * "empty_option": "Select Account",
	 * "target_class":"Account\Entity\Account",
	 * "property": "description"
	 * })
	 */
	private $account;

	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection @ORM\OneToMany(
	 *      targetEntity="Lead\Entity\LeadAttributeValue",
	 *      mappedBy="lead",
	 *      fetch="EXTRA_LAZY",
	 *      cascade={"persist", "remove"}
	 *      )
	 *      @ORM\OrderBy({"attribute" = "ASC"})
	 *     
	 *      @Annotation\Exclude()
	 */
	protected $attributes;

	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection @ORM\OneToMany(
	 *      targetEntity="Event\Entity\LeadEvent",
	 *      mappedBy="lead",
	 *      fetch="EXTRA_LAZY",
	 *      cascade={"persist", "remove"}
	 *      )
	 *      @ORM\OrderBy({"id" = "DESC"})
	 *     
	 *      @Annotation\Exclude()
	 */
	protected $events;

	/**
	 * Initialies the array variables.
	 */
	public function __construct ()
	{
		$this->attributes = new ArrayCollection();
		$this->events = new ArrayCollection();
		$this->timecreated = new \DateTime();
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId ()
	{
		return $this->id;
	}

	/**
	 * Set timecreated
	 *
	 * @param \DateTime $timecreated        	
	 *
	 * @return Lead
	 */
	public function setTimecreated ($timecreated)
	{
		$this->timecreated = $timecreated;
		
		return $this;
	}

	/**
	 * Get timecreated
	 *
	 * @return \DateTime
	 */
	public function getTimecreated ()
	{
		return $this->timecreated;
	}

	/**
	 * Set referrer
	 *
	 * @param string $referrer        	
	 *
	 * @return Lead
	 */
	public function setReferrer ($referrer)
	{
		$this->referrer = $referrer;
		
		return $this;
	}

	/**
	 * Get referrer
	 *
	 * @return string
	 */
	public function getReferrer ()
	{
		return $this->referrer;
	}

	/**
	 * Set ipaddress
	 *
	 * @param string $ipaddress        	
	 *
	 * @return Lead
	 */
	public function setIpaddress ($ipaddress)
	{
		$this->ipaddress = $ipaddress;
		
		return $this;
	}

	/**
	 * Get ipaddress
	 *
	 * @return string
	 */
	public function getIpaddress ()
	{
		return $this->ipaddress;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription ()
	{
		$result = "";
		$keys = [
				'FirstName'
		];
		// 'LastName'
		
		$fields = [
				'FirstName',
				// 'LastName',
				'ipaddress',
				'domain',
				'date'
		];
		$description = [];
		foreach ($keys as $key) {
			$leadAttribute = $this->findAttribute("FirstName");
			if ($leadAttribute)
				$description[$key] = $leadAttribute->getValue();
		}
		$description['domain'] = parse_url($this->getReferrer(), PHP_URL_HOST);
		$description['ipaddress'] = $this->getIpaddress();
		$description['date'] = date_format($this->getTimecreated(), 'm.d.Y');
		foreach ($fields as $field) {
			if (isset($description[$field])) {
				switch ($field) {
					case 'LastName':
						$result .= " {$description[$field]}";
						break;
					case 'domain':
						$result .= " from {$description[$field]}";
						break;
					case 'ipaddress':
						$result .= "@{$description[$field]}";
						break;
					case 'date':
						$result .= " on {$description[$field]}";
						break;
					default:
						$result .= $description[$field];
						break;
				}
			}
		}
		return $result;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getShortDescription ()
	{
		$result = "";
		$keys = [
				'FirstName'
		];
		
		$fields = [
				'FirstName',
				'ipaddress'
		];
		$description = [];
		foreach ($keys as $key) {
			$leadAttribute = $this->findAttribute($key);
			if ($leadAttribute)
				$description[$key] = $leadAttribute->getValue();
		}
		$description['ipaddress'] = $this->getIpaddress();
		foreach ($fields as $field) {
			if (isset($description[$field])) {
				switch ($field) {
					case 'LastName':
						$result .= " {$description[$field]}";
						break;
					case 'ipaddress':
						$result .= "@{$description[$field]}";
						break;
					default:
						$result .= $description[$field];
						break;
				}
			}
		}
		return $result;
	}

	public function getFullName ()
	{
		$result = "";
		$keys = [
				'FirstName',
				'LastName'
		];
		
		$fields = [
				'FirstName',
				'LastName'
		];
		$description = [];
		foreach ($keys as $key) {
			$leadAttribute = $this->findAttribute($key);
			if ($leadAttribute)
				$description[$key] = $leadAttribute->getValue();
		}
		foreach ($fields as $field) {
			if (isset($description[$field])) {
				switch ($field) {
					case 'LastName':
						$result .= " {$description[$field]}";
						break;
					default:
						$result .= $description[$field];
						break;
				}
			}
		}
		return $result;
	}

	/**
	 *
	 * @param string $attributeName        	
	 *
	 * @return \Lead\Entity\LeadAttributeValue|boolean
	 */
	public function findAttribute ($attributeName)
	{
		$attribute = false;
		$attributes = $this->findAttributes($attributeName);
		if ($attributes->count() > 0) {
			$attribute = $attributes->first();
		}
		return $attribute;
	}

	/**
	 *
	 * @param string $attributeName        	
	 *
	 * @return ArrayCollection
	 */
	public function findAttributes ($attributeName)
	{
		return $this->getAttributes(true)->filter(
				function  ($leadAttribute) use( $attributeName)
				{
					return $leadAttribute->getAttribute()
						->getAttributeName() == $attributeName;
				});
	}

	/**
	 *
	 * @return \Account\Entity\Account
	 */
	public function getAccount ()
	{
		return $this->account;
	}

	/**
	 *
	 * @param \Account\Entity\Account $account        	
	 */
	public function setAccount ($account)
	{
		$this->account = $account;
		
		return $this;
	}

	/**
	 * Get ipaddress
	 *
	 * @return string
	 */
	public function getAccountDescription ()
	{
		return $this->account->getDescription();
	}

	/**
	 * Get attributes.
	 *
	 * @return array|ArrayCollection
	 */
	public function getAttributes ($ac = false)
	{
		return $ac ? $this->attributes : $this->attributes->getValues();
	}

	/**
	 * Add an attribute to the lead.
	 *
	 * @param \Lead\Entity\LeadAttributeValue $attribute        	
	 *
	 * @return Lead
	 */
	public function addAttribute (LeadAttributeValue $attribute)
	{
		if (! $this->attributes->contains($attribute)) {
			$this->attributes->add($attribute);
			$attribute->setLead($this);
		}
		
		return $this;
	}

	/**
	 * Add attributes to the lead.
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $attributes        	
	 *
	 * @return void
	 */
	public function addAttributes (ArrayCollection $attributes)
	{
		foreach ($attributes as $attribute) {
			if (! $this->attributes->contains($attribute)) {
				$this->attributes->add($attribute);
				$attribute->setLead($this);
			}
		}
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $attributes        	
	 *
	 * @return Lead
	 */
	public function removeAttributes (ArrayCollection $attributes)
	{
		foreach ($attributes as $attribute) {
			if ($this->attributes->contains($attribute)) {
				$this->attributes->removeElement($attribute);
				$attribute->setLead(null);
			}
		}
		
		return $this;
	}

	/**
	 *
	 * @param string $eventName        	
	 *
	 * @return \Lead\Entity\LeadEvent|boolean
	 */
	public function findEvent ($eventName)
	{
		$event = false;
		$events = $this->findEvents($eventName);
		if ($events->count() > 0) {
			$attribute = $events->first();
		}
		return $event;
	}

	/**
	 *
	 * @param string $eventName        	
	 *
	 * @return ArrayCollection
	 */
	public function findEvents ($eventName)
	{
		return $this->getEvents(true)->filter(
				function  (\Event\Entity\LeadEvent $event) use( $eventName)
				{
					return $event->getEvent()
						->getEvent() == $eventName;
				});
	}

	/**
	 *
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getEvents ($ac = false)
	{
		return $ac ? $this->events : $this->events->getValues();
	}

	/**
	 * Add events to the lead.
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $events        	
	 *
	 * @return void
	 */
	public function addEvents (ArrayCollection $events)
	{
		foreach ($events as $event) {
			if (! $this->events->contains($event)) {
				$this->events->add($events);
				$event->setLead($this);
			}
		}
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $events        	
	 *
	 * @return Lead
	 */
	public function removeEvents (ArrayCollection $events)
	{
		foreach ($events as $event) {
			if ($this->events->contains($event)) {
				$this->events->removeElement($event);
				$event->setLead(null);
			}
		}
		
		return $this;
	}

	/**
	 * Get string equivalent.
	 *
	 * @return string
	 */
	public function __toString ()
	{
		return $this->getDescription();
	}
}
