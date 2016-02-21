<?php

namespace Lead\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;
use Application\Provider\EntityDataTrait;
use Doctrine\Common\Collections\Collection;
use Report\Entity\Report;
use JMS\Serializer\Annotation as JMS;
use JMS\Serializer\Annotation\MaxDepth;
use Doctrine\Search\Mapping\Annotations as MAP;
use Application\Service\ElasticSearch\SearchableEntityInterface;
use Application\Provider\SearchManagerAwareTrait;
use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Elastica;
use Agent;

/**
 * Lead
 *
 * @ORM\Table(name="lead", indexes={@ORM\Index(name="id", columns={"id"})})
 * @ORM\Entity(repositoryClass="Lead\Entity\Repository\LeadRepository")
 * @ORM\EntityListeners({ "Lead\Entity\Listener\LeadListener" })
 * @Annotation\Instance("\Lead\Entity\Lead")
 * @JMS\ExclusionPolicy("all")
 * @MAP\ElasticSearchable(index="reports", type="lead", source=true)
 */
class Lead implements SearchableEntityInterface, ServiceLocatorAwareInterface {
	use EntityDataTrait, SearchManagerAwareTrait, ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 *      @Annotation\Exclude()
	 *      @MAP\Id
	 *      @JMS\Type("integer")
	 *      @JMS\Expose @JMS\Groups({"list", "details"})
	 */
	private $id;
	
	/**
	 *
	 * @var \DateTime @ORM\Column(name="timecreated", type="datetime",
	 *      nullable=false)
	 *      @Annotation\Exclude()
	 *      @JMS\Type("DateTime")
	 *      @JMS\Expose @JMS\Groups({"list", "details"})
	 *      @MAP\ElasticField(
	 *      type="date",
	 *      includeInAll=true,
	 *      )
	 */
	private $timecreated;
	
	/**
	 *
	 * @var string @ORM\Column(name="referrer", type="string", length=255,
	 *      nullable=false)
	 *      @Annotation\Exclude()
	 *      @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"list", "details"})
	 *      @MAP\ElasticField(
	 *      type="string",
	 *      includeInAll=true
	 *      )
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
	 * @var string @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"list", "details"})
	 *      @MAP\ElasticField(type="ip", includeInAll=true, index="no",
	 *      store=true, nullValue=null)
	 *     
	 */
	private $ipv4address;
	
	/**
	 *
	 * @var string @Annotation\Exclude()
	 *      @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"details", "attributes"})
	 *      @MAP\ElasticField(type="multi_field", fields={
	 *      @MAP\ElasticField(name="description", type="string",
	 *      includeInAll=true, analyzer="whitespace"),
	 *      @MAP\ElasticField(name="exact", type="string",
	 *      includeInAll=false, index="not_analyzed")
	 *      })
	 */
	private $description;
	
	/**
	 *
	 * @var \Account\Entity\Account @ORM\ManyToOne(
	 *      targetEntity="Account\Entity\Account",
	 *      inversedBy="leads",
	 *      cascade={"persist", "remove"},
	 *      fetch="EXTRA_LAZY",
	 *      )
	 *      @ORM\JoinColumn(
	 *      name="account_id",
	 *      referencedColumnName="id",
	 *      nullable=true,
	 *      )
	 *      @ORM\OrderBy({"name" = "ASC"})
	 *      @Annotation\Instance("\Account\Entity\Account")
	 *      @Annotation\Type("DoctrineModule\Form\Element\ObjectSelect")
	 *      @Annotation\Filter({"name":"StripTags"})
	 *      @Annotation\Filter({"name":"StringTrim"})
	 *      @Annotation\Validator({"name":"Digits"})
	 *      @Annotation\Required(false)
	 *      @Annotation\Options({
	 *      "required":"false",
	 *      "label":"Account",
	 *      "empty_option": "Select Account",
	 *      "target_class":"Account\Entity\Account",
	 *      "property": "description"
	 *      })
	 *      @JMS\Type("Account\Entity\Account")
	 *      @JMS\Expose @JMS\Groups({"list", "details"})
	 *      @MaxDepth(1)
	 *      @MAP\ElasticField(type="nested", nullValue=null, properties={
	 *      @MAP\ElasticField(name="id", type="integer", includeInAll=false,
	 *      index="not_analyzed"),
	 *      @MAP\ElasticField(name="name", type="string", includeInAll=true)
	 *      })
	 */
	private $account;
	
	/**
	 *
	 * @var \DateTime @ORM\Column(name="lastsubmitted", type="datetime",
	 *      nullable=true)
	 *      @Annotation\Exclude()
	 *      @JMS\Type("DateTime")
	 *      @JMS\Expose @JMS\Groups({"list", "details"})
	 *      @MAP\ElasticField(
	 *      type="date",
	 *      includeInAll=true,
	 *      nullValue=null
	 *      )
	 */
	protected $lastsubmitted;
	
	/**
	 *
	 * @var string @ORM\Column(name="locality", type="string", length=255,
	 *      nullable=true)
	 *      @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"list", "details"})
	 *      @MAP\ElasticField(
	 *      type="geo_point",
	 *      nullValue=null
	 *      )
	 */
	protected $locality;
	
	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection @ORM\OneToMany(
	 *      targetEntity="Lead\Entity\LeadAttributeValue",
	 *      mappedBy="lead",
	 *      fetch="EXTRA_LAZY",
	 *      cascade={"persist", "remove"}
	 *      )
	 *      @ORM\OrderBy({"attribute" = "ASC"})
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
	 *
	 * @var Collection
	 */
	protected $reports;

	/**
	 * Initialies the array variables.
	 */
	public function __construct()
	{
		$this->attributes = new ArrayCollection();
		$this->events = new ArrayCollection();
		$this->reports = new ArrayCollection();
		$this->timecreated = new \DateTime();
	}

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
	 * Set timecreated
	 *
	 * @param \DateTime $timecreated        	
	 *
	 * @return Lead
	 */
	public function setTimecreated($timecreated)
	{
		$this->timecreated = $timecreated;
		
		return $this;
	}

	/**
	 * Get timecreated
	 *
	 * @return \DateTime
	 */
	public function getTimecreated()
	{
		return $this->timecreated;
	}

	/**
	 *
	 * @return \DateTime $lastsubmitted
	 */
	public function getLastsubmitted()
	{
		return $this->lastsubmitted;
	}

	/**
	 *
	 * @param DateTime $lastsubmitted        	
	 *
	 * @return Lead
	 */
	public function setLastsubmitted($lastsubmitted)
	{
		$this->lastsubmitted = $lastsubmitted;
		return $this;
	}

	/**
	 * Set referrer
	 *
	 * @param string $referrer        	
	 *
	 * @return Lead
	 */
	public function setReferrer($referrer)
	{
		$this->referrer = $referrer;
		
		return $this;
	}

	/**
	 * Get referrer
	 *
	 * @return string
	 */
	public function getReferrer()
	{
		return $this->add_http($this->referrer);
	}

	/**
	 * Set ipaddress
	 *
	 * @param string $ipaddress        	
	 *
	 * @return Lead
	 */
	public function setIpaddress($ipaddress)
	{
		$this->ipaddress = $ipaddress;
		
		return $this;
	}

	/**
	 * Get ipaddress
	 *
	 * @return string
	 */
	public function getIpaddress()
	{
		return $this->ipaddress;
	}

	/**
	 * Set ipv4address
	 *
	 * @param string $ipv4address        	
	 *
	 * @return Lead
	 */
	public function setIpv4address($ipv4address)
	{
		$this->ipv4address = $ipv4address;
		
		return $this;
	}

	/**
	 * Get ipv4address
	 *
	 * @return string
	 */
	public function getIpv4address()
	{
		return $this->ipv4address;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription()
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
		$description = [ ];
		foreach ( $keys as $key ) {
			$leadAttribute = $this->findAttribute("FirstName");
			if ($leadAttribute)
				$description [$key] = $leadAttribute->getValue();
		}
		$description ['domain'] = parse_url($this->getReferrer(), PHP_URL_HOST);
		$description ['ipaddress'] = $this->getIpaddress();
		$description ['date'] = date_format($this->getTimecreated(), 'm.d.Y');
		foreach ( $fields as $field ) {
			if (isset($description [$field])) {
				switch ($field) {
					case 'LastName' :
						$result .= " {$description[$field]}";
						break;
					case 'domain' :
						$result .= " from {$description[$field]}";
						break;
					case 'ipaddress' :
						$result .= "@{$description[$field]}";
						break;
					case 'date' :
						$result .= " on {$description[$field]}";
						break;
					default :
						$result .= $description [$field];
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
	public function getShortDescription()
	{
		$result = "";
		$keys = [ 
				'FirstName' 
		];
		
		$fields = [ 
				'FirstName',
				'ipaddress' 
		];
		$description = [ ];
		foreach ( $keys as $key ) {
			$leadAttribute = $this->findAttribute($key);
			if ($leadAttribute)
				$description [$key] = $leadAttribute->getValue();
		}
		$description ['ipaddress'] = $this->getIpaddress();
		foreach ( $fields as $field ) {
			if (isset($description [$field])) {
				switch ($field) {
					case 'LastName' :
						$result .= " {$description[$field]}";
						break;
					case 'ipaddress' :
						$result .= "@{$description[$field]}";
						break;
					default :
						$result .= $description [$field];
						break;
				}
			}
		}
		return $result;
	}

	public function getFullName()
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
		$description = [ ];
		foreach ( $keys as $key ) {
			$leadAttribute = $this->findAttribute($key);
			if ($leadAttribute)
				$description [$key] = $leadAttribute->getValue();
		}
		foreach ( $fields as $field ) {
			if (isset($description [$field])) {
				switch ($field) {
					case 'LastName' :
						$result .= " {$description[$field]}";
						break;
					default :
						$result .= $description [$field];
						break;
				}
			}
		}
		return $result;
	}

	/**
	 *
	 * @param string $attributeName        	
	 * @param bool $desc        	
	 *
	 * @return \Lead\Entity\LeadAttributeValue|boolean
	 */
	public function findAttribute($attributeName, $desc = false)
	{
		$attribute = false;
		$attributes = $this->findAttributes($attributeName, $desc);
		if ($attributes->count() > 0) {
			$attribute = $attributes->first();
		}
		return $attribute;
	}

	/**
	 *
	 * @param string $attributeName        	
	 * @param bool $desc        	
	 *
	 * @return ArrayCollection
	 */
	public function findAttributes($attributeName, $desc = false)
	{
		$attributes = $this->getAttributes(true);
		if (!$attributes instanceof Collection) {
			$attributes = new ArrayCollection($attributes);
		}
		return $attributes->filter(function ($leadAttribute) use($attributeName, $desc) {
			$attribute = $leadAttribute->getAttribute();
			if ($attribute) {
				$name = $desc ? $attribute->getAttributeDesc() : $attribute->getAttributeName();
				return preg_match('/' . preg_quote($attributeName, '/') . '/i', $name);
			}
			return false;
		});
	}

	/**
	 *
	 * @return \Account\Entity\Account
	 */
	public function getAccount()
	{
		return $this->account;
	}

	/**
	 *
	 * @param \Account\Entity\Account $account        	
	 */
	public function setAccount($account)
	{
		if (null === $account && $this->account) {
			$this->account->removeLeads($this);
		}
		$this->account = $account;
		
		return $this;
	}

	/**
	 * Get ipaddress
	 *
	 * @return string
	 */
	public function getAccountDescription()
	{
		return $this->account->getDescription();
	}

	/**
	 * Get attributes.
	 *
	 * @return array|ArrayCollection
	 */
	public function getAttributes($ac = false)
	{
		return $ac ? $this->attributes : $this->attributes->getValues();
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\Collection $attributes        	
	 *
	 * @return Lead
	 */
	public function setAttributes($attributes)
	{
		$this->attributes = new ArrayCollection();
		foreach ( $attributes as $attribute ) {
			if (!$this->attributes->contains($attribute)) {
				$this->attributes->add($attribute);
				if ($attribute->getLead() !== $this) {
					$attribute->setLead($this);
				}
			}
		}
		
		return $this;
	}

	/**
	 * Add an attribute to the lead.
	 *
	 * @param \Lead\Entity\LeadAttributeValue $attribute        	
	 *
	 * @return Lead
	 */
	public function addAttribute(LeadAttributeValue $attribute)
	{
		if (!$this->attributes->contains($attribute)) {
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
	public function addAttributes(ArrayCollection $attributes)
	{
		foreach ( $attributes as $attribute ) {
			if (!$this->attributes->contains($attribute)) {
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
	public function removeAttributes(ArrayCollection $attributes)
	{
		foreach ( $attributes as $attribute ) {
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
	public function findEvent($eventName)
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
	public function findEvents($eventName)
	{
		return $this->getEvents(true)
			->filter(function (\Event\Entity\LeadEvent $event) use($eventName) {
			return $event->getEvent()
				->getEvent() == $eventName;
		});
	}

	/**
	 *
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getEvents($ac = false)
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
	public function addEvents(ArrayCollection $events)
	{
		foreach ( $events as $event ) {
			if (!$this->events->contains($event)) {
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
	public function removeEvents(ArrayCollection $events)
	{
		foreach ( $events as $event ) {
			if ($this->events->contains($event)) {
				$this->events->removeElement($event);
				$event->setLead(null);
			}
		}
		
		return $this;
	}

	/**
	 *
	 * @param bool $ac        	
	 *
	 * @return \Doctrine\Common\Collections\Collection $reports
	 */
	public function getReports($ac = false)
	{
		$reports = $this->reports;
		return $ac ? $reports : ($reports ? $reports->getValues() : [ ]);
	}

	/**
	 *
	 * @param Collection $reports        	
	 *
	 * @return Lead
	 */
	public function setReports($reports)
	{
		$this->reports = $reports;
		return $this;
	}

	/**
	 * Add reports to the lead.
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $reports        	
	 *
	 * @return void
	 */
	public function addReports(Collection $reports)
	{
		foreach ( $reports as $report ) {
			if (!$this->reports->contains($report)) {
				$this->reports->add($report);
				$report->addLeads([ 
						$this 
				]);
			}
		}
	}

	/**
	 *
	 * @param Collection $reports        	
	 *
	 * @return Lead
	 */
	public function removeReports(Collection $reports)
	{
		foreach ( $reports as $report ) {
			if ($this->reports->contains($report)) {
				$this->reports->removeElement($report);
				$report->removeLeads([ 
						$this 
				]);
			}
		}
		
		return $this;
	}

	/**
	 * Set Description
	 *
	 * @param string $description        	
	 *
	 * @return Lead
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getLocality()
	{
		if (!isset($this->locality)) {
			$result = null;
			$fields = [ 
					'city',
					'state',
					'zip' 
			];
			$location = array_combine($fields, array_pad([ ], count($fields), null));
			foreach ( $fields as $field ) {
				foreach ( [ 
						ucwords($field),
						$field 
				] as $name ) {
					$attribute = $this->findAttribute($name);
					if ($attribute) {
						$location [$field] = $attribute->getValue();
						break 1;
					}
				}
			}
			
			$location = array_filter($location);
			if (count($location) > 1 || isset($location ['zip'])) {
				try {
					$localityQuery = new Agent\Elastica\Query\LocalityQuery($this->getServiceLocator());
					$locality = $localityQuery->request($location);
					if ($locality) {
						$result = $this->locality = implode(",", $locality ['_source'] ['latlon']);
					}
				} catch ( \Exception $e ) {
					return $result;
				}
			}
		}
		return $this->locality;
	}

	/**
	 *
	 * @param string $locality        	
	 *
	 * @return Lead
	 */
	public function setLocality($locality)
	{
		$this->locality = $locality;
		return $this;
	}

	/**
	 * Add http
	 *
	 * @param string $referrer        	
	 */
	protected function add_http($referrer)
	{
		if (!preg_match("~^(?:f|ht)tps?://~i", $referrer)) {
			$referrer = "http://" . $referrer;
		}
		return $referrer;
	}

	/**
	 * Get string equivalent.
	 *
	 * @return string
	 */
	public function __toString()
	{
		try {
			$desc = $this->getDescription();
		} catch ( \Exception $e ) {
			$desc = "";
		}
		return $desc;
	}
}
