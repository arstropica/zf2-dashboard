<?php

namespace Report\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\ArrayCollection;
use Agent\Entity\Agent;
use Account\Entity\Account;
use Lead\Entity\Lead;
use Application\Provider\EntityDataTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Application\Provider\ObjectManagerAwareTrait;
use Application\Provider\FlashMessengerAwareTrait;
use Application\Provider\ElasticaAwareTrait;
use Report\Provider\ResultAwareTrait;

/**
 * Report
 *
 * @ORM\Table(name="report")
 * @ORM\Entity
 * @Annotation\Instance("\Report\Entity\Report")
 */
class Report implements ServiceLocatorAwareInterface, ObjectManagerAwareInterface {
	
	use ServiceLocatorAwareTrait, EntityDataTrait, ObjectManagerAwareTrait, FlashMessengerAwareTrait, ElasticaAwareTrait, ResultAwareTrait;
	
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
	 * @var string @ORM\Column(name="scope", type="string", length=255,
	 *      nullable=true)
	 */
	private $name;
	
	/**
	 *
	 * @var \DateTime @ORM\Column(name="updated", type="datetime",
	 *      nullable=false)
	 *      @Annotation\Exclude()
	 */
	private $updated;
	
	/**
	 *
	 * @var \Agent\Entity\Agent @ORM\OneToOne(
	 *      targetEntity="Agent\Entity\Agent",
	 *      mappedBy="report",
	 *      cascade={"persist", "remove"},
	 *      fetch="EXTRA_LAZY",
	 *      )
	 *      @ORM\JoinColumn(
	 *      name="agent_id",
	 *      referencedColumnName="id",
	 *      onDelete="CASCADE"
	 *      )
	 */
	private $agent;
	
	/**
	 *
	 * @var \Account\Entity\Account @ORM\ManyToOne(
	 *      targetEntity="Account\Entity\Account",
	 *      inversedBy="reports",
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
	 */
	private $account;
	
	/**
	 *
	 * @var Collection @Annotation\Exclude()
	 */
	private $results;
	
	/**
	 *
	 * @var Collection
	 */
	private $relevance;
	
	/**
	 *
	 * @var Collection @ORM\OneToMany(
	 *      targetEntity="Event\Entity\ReportEvent",
	 *      mappedBy="report",
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
		$dateTime = date('Y-m-d H:i:s');
		$this->agent = new Agent();
		$this->results = new ArrayCollection();
		$this->events = new ArrayCollection();
		$this->setName("New Report (" . $dateTime . ")");
		$this->setUpdated(new \DateTime("now"));
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
	 * @return Report
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 *
	 * @return string $name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 *
	 * @param string $name        	
	 *
	 * @return Report
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 * @return Report
	 */
	public function setUpdated($updated)
	{
		$this->updated = $updated;
		return $this;
	}

	/**
	 *
	 * @return Agent $agent
	 */
	public function getAgent()
	{
		return $this->agent;
	}

	/**
	 *
	 * @param \Agent\Entity\Agent $agent        	
	 *
	 * @return Report
	 */
	public function setAgent($agent)
	{
		$this->agent = $agent;
		$agent->setReport($this);
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
	 * @param \Account\Entity\Account $account        	
	 *
	 * @return Report
	 */
	public function setAccount($account)
	{
		$this->account = $account;
		if ($account && !$account->getReports(true)
			->contains($this)) {
			$account->addReports(new ArrayCollection([ 
					$this 
			]));
		}
		return $this;
	}

	/**
	 *
	 * @param bool $ac        	
	 *
	 * @return Collection $results
	 */
	public function getResults($ac = false, $limit = null, $sort = '_score', $order = 'desc')
	{
		$results = $this->results;
		if (!$results instanceof Collection || !$results->count()) {
			$data = $this->generateResults($this, $limit, $sort, $order, false);
			if ($data) {
				$this->setResults($data);
				$results = $this->results;
			} else {
				$this->setResults(new ArrayCollection());
			}
		}
		if (!$ac) {
			$results = $results->getValues();
		}
		return $results;
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\Collection $results        	
	 *
	 * @return Report
	 */
	public function setResults($results)
	{
		$this->results = $results;
		return $this;
	}

	/**
	 * Add a result to the report.
	 *
	 * @param Collection $results        	
	 *
	 * @return void
	 */
	public function addResults($results)
	{
		foreach ( $results as $result ) {
			if (!$this->results->contains($result)) {
				$this->results->add($result);
				$result->addReports([ 
						$this 
				]);
			}
		}
	}

	/**
	 * Remove results from the report.
	 *
	 * @param Collection $results        	
	 *
	 * @return Report
	 */
	public function removeResults($results)
	{
		foreach ( $results as $result ) {
			if ($this->results->contains($result)) {
				$this->results->removeElement($result);
				$result->removeReports([ 
						$this 
				]);
			}
		}
		
		return $this;
	}

	/**
	 *
	 * @param integer $id        	
	 *
	 * @return null|Result
	 */
	public function findResult($id)
	{
		$results = $this->getResults(true);
		$result = null;
		if ($results && $results instanceof Collection) {
			$filtered = $results->filter(function ($r) use($id) {
				return ($r instanceof Result) ? $r->getLead()
					->getId() == $id : false;
			});
			if ($filtered && $filtered->count() > 0) {
				$result = $filtered->first();
			}
		}
		return $result;
	}

	/**
	 *
	 * @param bool $ac        	
	 *
	 * @return Collection $events
	 */
	public function getEvents($ac = false)
	{
		return $ac ? $this->events : $this->events->getValues();
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\Collection $events        	
	 *
	 * @return Report
	 */
	public function setEvents($events)
	{
		$this->events = $events;
		
		return $this;
	}

	/**
	 * Add events to the report.
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
				$event->setReport($this);
			}
		}
	}

	/**
	 *
	 * @param Collection $events        	
	 *
	 * @return Report
	 */
	public function removeEvents(Collection $events)
	{
		foreach ( $events as $event ) {
			if ($this->events->contains($event)) {
				$this->events->removeElement($event);
				$event->setReport(null);
			}
		}
		
		return $this;
	}

	/**
	 * Get results count
	 *
	 * @return integer
	 */
	public function getCount()
	{
		$results = $this->getResults(true);
		return $results ? $results->count() : 0;
	}
}

?>