<?php

namespace Agent\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Application\Provider\EntityDataTrait;
use Agent\Entity\Filters\AccountFilter;
use Agent\Entity\Filters\DateFilter;
use Doctrine\Common\Collections\Collection;

/**
 * Filter
 *
 * @ORM\Table(name="agent_filter")
 * @ORM\Entity
 * @Annotation\Instance("\Agent\Entity\Filter")
 */
class Filter {
	
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
	 * @var Collection @ORM\OneToMany(targetEntity="Agent\Entity\Agent",
	 *      mappedBy="filter", cascade={"persist"})
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $agents;
	
	/**
	 *
	 * @var AccountFilter @ORM\ManyToOne(targetEntity="Agent\Entity\Filters\AccountFilter",
	 *      inversedBy="filters",
	 *      cascade={"persist", "remove"})
	 *      @ORM\JoinColumn(name="account_filter_id", referencedColumnName="id")
	 *      @Annotation\ComposedObject("Agent\Entity\Filters\AccountFilter")
	 *      @Annotation\Options({
	 *      "column-size":"xs-12",
	 *      "label":"1. Filter by Account: ",
	 *      })
	 *      @Annotation\Attributes({
	 *      "id":"accountFilter",
	 *      "class":"collection-fieldset",
	 *      })
	 */
	private $accountFilter;
	
	/**
	 *
	 * @var DateFilter @ORM\ManyToOne(targetEntity="Agent\Entity\Filters\DateFilter",
	 *      inversedBy="filters",
	 *      cascade={"persist", "remove"})
	 *      @ORM\JoinColumn(name="date_filter_id", referencedColumnName="id")
	 *      @Annotation\ComposedObject("Agent\Entity\Filters\DateFilter")
	 *      @Annotation\Options({
	 *      "column-size":"xs-12",
	 *      "label":"2. Filter by Date: ",
	 *      })
	 *      @Annotation\Attributes({
	 *      "id":"dateFilter",
	 *      "class":"collection-fieldset",
	 *      })
	 */
	private $dateFilter;

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
	 * @return Filter
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 *
	 * @return Collection $agents
	 */
	public function getAgents()
	{
		return $this->agents;
	}

	/**
	 *
	 * @param Collection $agents        	
	 *
	 * @return Filter
	 */
	public function setAgents($agents)
	{
		$this->agents = $agents;
		return $this;
	}

	/**
	 * Add $agents to the filter.
	 *
	 * @param Collection $agents        	
	 *
	 * @return void
	 */
	public function addAgents(Collection $agents)
	{
		foreach ( $agents as $agent ) {
			if (!$this->agents->contains($agent)) {
				$this->agent->add($agent);
				$agent->setFilter($this);
			}
		}
	}

	/**
	 *
	 * @param Collection $agents        	
	 *
	 * @return Filter
	 */
	public function removeAgents(Collection $agents)
	{
		foreach ( $agents as $agent ) {
			if ($this->agents->contains($agent)) {
				$this->agents->removeElement($agent);
				$agent->setFilter(null);
			}
		}
		
		return $this;
	}

	/**
	 *
	 * @return AccountFilter $accountFilter
	 */
	public function getAccountFilter()
	{
		return $this->accountFilter;
	}

	/**
	 *
	 * @param \Agent\Entity\Filters\AccountFilter $accountFilter        	
	 *
	 * @return Filter
	 */
	public function setAccountFilter($accountFilter)
	{
		$this->accountFilter = $accountFilter;
		return $this;
	}

	/**
	 *
	 * @return DateFilter $dateFilter
	 */
	public function getDateFilter()
	{
		return $this->dateFilter;
	}

	/**
	 *
	 * @param \Agent\Entity\Filters\DateFilter $dateFilter        	
	 *
	 * @return Filter
	 */
	public function setDateFilter($dateFilter)
	{
		$this->dateFilter = $dateFilter;
		return $this;
	}

}

?>