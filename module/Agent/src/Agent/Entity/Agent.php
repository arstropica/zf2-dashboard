<?php

namespace Agent\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;
use Application\Provider\EntityDataTrait;
use Account\Entity\Account;

/**
 * Agent
 *
 * @ORM\Table(name="agent")
 * @ORM\Entity
 * @Annotation\Instance("\Agent\Entity\Agent")
 */
class Agent {
	
	use EntityDataTrait;
	
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
	 * @var \DateTime @ORM\Column(name="updated", type="datetime",
	 *      nullable=false)
	 *      @Annotation\Exclude()
	 */
	private $updated;
	
	/**
	 *
	 * @var string @ORM\Column(name="scope", type="string", length=45,
	 *      nullable=false)
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $scope;
	
	/**
	 *
	 * @var \Account\Entity\Account @ORM\ManyToOne(targetEntity="Account\Entity\Account")
	 *      @ORM\JoinColumn(name="account_id",
	 *      referencedColumnName="id",
	 *      nullable=true
	 *      )
	 *      @Annotation\Instance("\Account\Entity\Account")
	 *      @Annotation\Type("DoctrineModule\Form\Element\ObjectSelect")
	 *      @Annotation\Filter({"name":"StripTags"})
	 *      @Annotation\Filter({"name":"StringTrim"})
	 *      @Annotation\Validator({"name":"Digits"})
	 *      @Annotation\Required(false)
	 *      @Annotation\Options({
	 *      "required":"false",
	 *      "label":"Account",
	 *      "empty_option": "Account Filter",
	 *      "target_class":"Account\Entity\Account",
	 *      "property": "description"
	 *      })
	 */
	private $account;
	
	/**
	 *
	 * @var integer @ORM\Column(name="orphan", type="integer", length=1,
	 *      nullable=true)
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $orphan;
	
	/**
	 *
	 * @var Collection @ORM\OneToMany(
	 *      targetEntity="Agent\Entity\AgentCriterion",
	 *      mappedBy="agent",
	 *      fetch="EXTRA_LAZY",
	 *      cascade={"persist", "remove"}
	 *      )
	 *     
	 *      @Annotation\Exclude()
	 */
	private $criteria;
	
	/**
	 *
	 * @var \Report\Entity\Report @ORM\OneToOne(targetEntity="Report\Entity\Report",
	 *      inversedBy="agent", cascade={"persist", "remove"})
	 *      @ORM\JoinColumn(name="report_id", referencedColumnName="id",
	 *      nullable=true, onDelete="CASCADE")
	 */
	private $report;
	
	/**
	 *
	 * @var Collection @ORM\OneToMany(
	 *      targetEntity="Event\Entity\AgentEvent",
	 *      mappedBy="agent",
	 *      fetch="EXTRA_LAZY",
	 *      indexBy="id",
	 *      cascade={"all","merge","persist","refresh","remove"}
	 *      )
	 *      @ORM\OrderBy({"id"="DESC"})
	 *      @Annotation\Exclude()
	 *     
	 */
	private $events;

	function __construct()
	{
		$this->criteria = new ArrayCollection();
		$this->events = new ArrayCollection();
		$this->scope = 'local';
		$this->updated = new \DateTime("now");
	}

	/**
	 *
	 * @return integer $id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @param integer $id        	
	 *
	 * @return Agent
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 *
	 * @return \Datetime $updated
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 *
	 * @param DateTime $updated        	
	 *
	 * @return Agent
	 */
	public function setUpdated($updated)
	{
		$this->updated = $updated;
		return $this;
	}

	/**
	 *
	 * @return string $scope
	 */
	public function getScope()
	{
		return $this->scope;
	}

	/**
	 *
	 * @param string $scope        	
	 *
	 * @return Agent
	 */
	public function setScope($scope)
	{
		$this->scope = $scope;
		return $this;
	}

	/**
	 *
	 * @return Account $account
	 */
	public function getAccount()
	{
		return $this->account;
	}

	/**
	 *
	 * @return integer $orphan
	 */
	public function getOrphan()
	{
		return $this->orphan;
	}

	/**
	 *
	 * @param \Account\Entity\Account $account        	
	 *
	 * @return Agent
	 */
	public function setAccount($account)
	{
		$this->account = $account;
		return $this;
	}

	/**
	 *
	 * @param integer $orphan        	
	 *
	 * @return Agent
	 */
	public function setOrphan($orphan)
	{
		$this->orphan = $orphan;
		return $this;
	}

	/**
	 *
	 * @param bool $ac        	
	 *
	 * @return mixed $criteria
	 */
	public function getCriteria($ac = false)
	{
		return $ac ? $this->criteria : $this->criteria->getValues();
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\Collection $criteria        	
	 *
	 * @return Agent
	 */
	public function setCriteria($criteria)
	{
		$this->criteria = $criteria;
		return $this;
	}

	/**
	 * Add criteria to the agent.
	 *
	 * @param Collection $criteria        	
	 *
	 * @return void
	 */
	public function addCriteria(Collection $criteria)
	{
		foreach ( $criteria as $criterion ) {
			if (!$this->criteria->contains($criterion)) {
				$this->criteria->add($criterion);
				$criterion->setAgent($this);
			}
		}
	}

	/**
	 *
	 * @param Collection $criteria        	
	 *
	 * @return Agent
	 */
	public function removeCriteria(Collection $criteria)
	{
		foreach ( $criteria as $criterion ) {
			if ($this->criteria->contains($criterion)) {
				$this->criteria->removeElement($criterion);
				$criterion->setAgent(null);
			}
		}
		
		return $this;
	}

	/**
	 *
	 * @return the $report
	 */
	public function getReport()
	{
		return $this->report;
	}

	/**
	 *
	 * @param \Report\Entity\Report $report        	
	 *
	 * @return Agent
	 */
	public function setReport($report)
	{
		$this->report = $report;
		return $this;
	}

	/**
	 *
	 * @param bool $ac        	
	 *
	 * @return mixed $events
	 */
	public function getEvents($ac = false)
	{
		return $ac ? $this->events : $this->events->getValues();
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\Collection $events        	
	 *
	 * @return Agent
	 */
	public function setEvents($events)
	{
		$this->events = $events;
		return $this;
	}

	/**
	 * Add events to the agent.
	 *
	 * @param Collection $events        	
	 *
	 * @return void
	 */
	public function addEvents(Collection $events)
	{
		foreach ( $events as $event ) {
			if (!$this->events->contains($event)) {
				$this->events->add($events);
				$event->setAgent($this);
			}
		}
	}

	/**
	 *
	 * @param Collection $events        	
	 *
	 * @return Agent
	 */
	public function removeEvents(Collection $events)
	{
		foreach ( $events as $event ) {
			if ($this->events->contains($event)) {
				$this->events->removeElement($event);
				$event->setAgent(null);
			}
		}
		
		return $this;
	}
}

?>