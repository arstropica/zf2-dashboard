<?php
namespace Account\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;

/**
 * Account
 *
 * @ORM\Table(name="account")
 * @ORM\Entity(repositoryClass="Account\Entity\Repository\AccountRepository")
 * @Annotation\Instance("\Account\Entity\Account")
 */
class Account
{

	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $id;

	/**
	 *
	 * @var string @ORM\Column(name="guid", type="string", length=255,
	 *      nullable=false)
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $guid;

	/**
	 *
	 * @var string @ORM\Column(name="name", type="string", length=255,
	 *      nullable=false)
	 *      @Annotation\Type("Zend\Form\Element\Text")
	 *      @Annotation\Required({"required":"true"})
	 *      @Annotation\Filter({"name":"StripTags"})
	 *      @Annotation\Options({"label":"Account Name"})
	 */
	private $name;

	/**
	 *
	 * @var string @ORM\Column(name="description", type="text", length=65535,
	 *      nullable=true)
	 *      @Annotation\Type("Zend\Form\Element\Text")
	 *      @Annotation\Required(false)
	 *      @Annotation\Filter({"name":"StripTags"})
	 *      @Annotation\Options({"label":"Description"})
	 */
	private $description;

	/**
	 *
	 * @var boolean @ORM\Column(name="active", type="boolean", nullable=false)
	 *      @Annotation\Type("Zend\Form\Element\Select")
	 *      @Annotation\Options({
	 *      "label":"Status:",
	 *      "value_options":{1: "Active", 0: "Disabled"}
	 *      })
	 */
	private $active = '1';

	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection @ORM\ManyToMany(
	 *      targetEntity="Api\Entity\Api",
	 *      fetch="EXTRA_LAZY",
	 *      cascade={"merge", "persist"},
	 *      )
	 *      @ORM\JoinTable(
	 *      name="api_accounts",
	 *      joinColumns={
	 *      @ORM\JoinColumn(
	 *      name="api_id",
	 *      referencedColumnName="id",
	 *      nullable=false,
	 *      )
	 *      },
	 *      inverseJoinColumns={
	 *      @ORM\JoinColumn(
	 *      name="account_id",
	 *      referencedColumnName="id",
	 *      nullable=false,
	 *      ),
	 *      }
	 *      )
	 *      @Annotation\Exclude()
	 */
	protected $apis;

	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection @ORM\OneToMany(
	 *      targetEntity="Lead\Entity\Lead",
	 *      mappedBy="account",
	 *      fetch="EXTRA_LAZY",
	 *      indexBy="id",
	 *      cascade={"remove"},
	 *      )
	 *      @ORM\JoinTable(
	 *      name="lead",
	 *      joinColumns={
	 *      @ORM\JoinColumn(
	 *      name="account_id",
	 *      referencedColumnName="id",
	 *      )
	 *      },
	 *      inverseJoinColumns={
	 *      @ORM\JoinColumn(
	 *      name="id",
	 *      referencedColumnName="id",
	 *      unique=true,
	 *      ),
	 *      @ORM\JoinColumn(
	 *      name="assigned",
	 *      referencedColumnName="timecreated",
	 *      unique=true
	 *      )
	 *      }
	 *      )
	 *      @ORM\OrderBy({"id"="DESC"})
	 *      @Annotation\Exclude()
	 */
	private $leads;

	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection @ORM\OneToMany(
	 *      targetEntity="Event\Entity\AccountEvent",
	 *      mappedBy="account",
	 *      fetch="EXTRA_LAZY",
	 *      indexBy="id",
	 *      cascade={"all","merge","persist","refresh","remove"}
	 *      )
	 *      @ORM\OrderBy({"id"="DESC"})
	 *      @Annotation\Exclude()
	 *     
	 */
	private $events;

	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection @ORM\OneToMany(
	 *      targetEntity="Api\Entity\ApiSetting",
	 *      mappedBy="account",
	 *      orphanRemoval=true,
	 *      fetch="EXTRA_LAZY",
	 *      indexBy="id",
	 *      cascade={"all","merge","persist","refresh","remove"}
	 *      )
	 *      @ORM\OrderBy({"id"="DESC"})
	 *      @Annotation\Exclude()
	 */
	private $apiSettings;

	/**
	 * Initialies the collection variables.
	 */
	public function __construct ()
	{
		$this->apis = new ArrayCollection();
		$this->apiSettings = new ArrayCollection();
		$this->leads = new ArrayCollection();
		$this->events = new ArrayCollection();
	}

	/**
	 * Set id
	 *
	 * @param integer $id        	
	 *
	 * @return Account
	 */
	public function setId ($id)
	{
		$this->id = $id;
		
		return $this;
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
	 * Set guid
	 *
	 * @param string $guid        	
	 *
	 * @return Account
	 */
	public function setGuid ($guid)
	{
		$this->guid = $guid;
		
		return $this;
	}

	/**
	 * Get guid
	 *
	 * @return string
	 */
	public function getGuid ()
	{
		return $this->guid;
	}

	/**
	 * Set name
	 *
	 * @param string $name        	
	 *
	 * @return Account
	 */
	public function setName ($name)
	{
		$this->name = $name;
		
		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName ()
	{
		return $this->name;
	}

	/**
	 * Set description
	 *
	 * @param string $description        	
	 *
	 * @return Account
	 */
	public function setDescription ($description)
	{
		$this->description = $description;
		
		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription ()
	{
		return $this->description;
	}

	/**
	 * Set active
	 *
	 * @param boolean $active        	
	 *
	 * @return Account
	 */
	public function setActive ($active)
	{
		$this->active = $active;
		
		return $this;
	}

	/**
	 * Get active
	 *
	 * @return boolean
	 */
	public function getActive ()
	{
		return $this->active;
	}

	/**
	 *
	 * @param string $apiName        	
	 *
	 * @return \Api\Entity\Api|boolean
	 */
	public function findApi ($apiName)
	{
		$api = false;
		$apis = $this->findApis($apiName);
		if ($apis->count() > 0) {
			$api = $apis->first();
		}
		return $api;
	}

	/**
	 *
	 * @param string $apiName        	
	 *
	 * @return ArrayCollection
	 */
	public function findApis ($apiName)
	{
		return $this->getApis(true)->filter(
				function  ($api) use( $apiName)
				{
					return $api->getName() == $apiName;
				});
	}

	/**
	 * Get apis.
	 *
	 * @return array
	 */
	public function getApis ($ac = false)
	{
		return $ac ? $this->apis : $this->apis->getValues();
	}

	/**
	 * Add apis to the account.
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $apis        	
	 *
	 * @return void
	 */
	public function addApis ($apis)
	{
		foreach ($apis as $api) {
			if (! $this->apis->contains($api)) {
				$this->apis->add($api);
			}
		}
	}

	/**
	 * Remove apis from the account.
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $apis        	
	 *
	 * @return Account
	 */
	public function removeApis (ArrayCollection $apis)
	{
		foreach ($apis as $api) {
			if ($this->apis->contains($api)) {
				$this->apis->removeElement($api);
			}
		}
		
		return $this;
	}

	/**
	 * Get leads.
	 *
	 * @return array
	 */
	public function getLeads ($ac = false)
	{
		return $ac ? $this->leads : $this->leads->getValues();
	}

	/**
	 * Add a lead to the account.
	 *
	 * @param \Lead\Entity\Lead $lead        	
	 *
	 * @return void
	 */
	public function addLead ($lead)
	{
		if (! $this->leads->contains($lead)) {
			$this->leads->add($lead);
		}
		$lead->setAccount($this);
	}

	/**
	 * Remove leads from the account.
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection|\Doctrine\ORM\PersistentCollection $leads        	
	 *
	 * @return Account
	 */
	public function removeLeads ($leads)
	{
		foreach ($leads as $lead) {
			if ($this->leads->contains($lead)) {
				$this->leads->removeElement($lead);
				$lead->setAccount(null);
			}
		}
		
		return $this;
	}

	/**
	 * Get events.
	 *
	 * @return array
	 */
	public function getEvents ()
	{
		return $this->events->getValues();
	}

	/**
	 * Add an event to the account.
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $events        	
	 *
	 * @return void
	 */
	public function addEvents (ArrayCollection $events)
	{
		foreach ($events as $event) {
			if (! $this->events->contains($event)) {
				$this->events->add($event);
				$event->setAccount($this);
			}
		}
	}

	/**
	 * Remove events from the account.
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $events        	
	 *
	 * @return Account
	 */
	public function removeEvents (ArrayCollection $events)
	{
		foreach ($events as $event) {
			if ($this->events->contains($event)) {
				$this->events->removeElement($event);
				$event->setAccount(null);
			}
		}
		
		return $this;
	}

	/**
	 * Get API Settings.
	 *
	 * @return array
	 */
	public function getApiSettings ()
	{
		return $this->apiSettings->getValues();
	}

	/**
	 * Set api settings.
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $apiSettings        	
	 *
	 * @return Account
	 */
	public function setApiSettings (ArrayCollection $apiSettings)
	{
		foreach ($apiSettings as $apiSetting) {
			if (! $this->apiSettings->contains($apiSetting)) {
				$this->apiSettings->add($apiSetting);
				$apiSetting->setAccount($this);
			}
		}
		return $this;
	}

	/**
	 * Add api settings to the account.
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $apiSettings        	
	 *
	 * @return void
	 */
	public function addApiSettings (ArrayCollection $apiSettings)
	{
		foreach ($apiSettings as $apiSetting) {
			if (! $this->apiSettings->contains($apiSetting)) {
				$this->apiSettings->add($apiSetting);
				$apiSetting->setAccount($this);
			}
		}
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $apiSettings        	
	 *
	 * @return Account
	 */
	public function removeApiSettings (ArrayCollection $apiSettings)
	{
		foreach ($apiSettings as $apiSetting) {
			if ($this->apiSettings->contains($apiSetting)) {
				$this->apiSettings->removeElement($apiSetting);
				$apiSetting->setAccount(null);
			}
		}
		
		return $this;
	}

	public function __toString ()
	{
		return $this->getName();
	}
}
