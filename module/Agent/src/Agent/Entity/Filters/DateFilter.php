<?php

namespace Agent\Entity\Filters;

use Doctrine\ORM\Mapping as ORM;
use Application\Provider\EntityDataTrait;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\Collection;

/**
 * DateFilter
 *
 * @Annotation\Instance("\Agent\Entity\Filters\DateFilter")
 * @ORM\Table(name="agent_filter_date")
 * @ORM\Entity
 */
class DateFilter {
	
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
	 * @var string @ORM\Column(name="mode", type="string", nullable=false)
	 *      @Annotation\Filter({"name":"StripTags"})
	 *      @Annotation\Filter({"name":"StringTrim"})
	 *      @Annotation\Required(false)
	 *      @Annotation\Type("Zend\Form\Element\Select")
	 *      @Annotation\Options({
	 *      "required":"false",
	 *      "label":"Filter by: ",
	 *      "empty_option": "None",
	 *      "value_options": {
	 *      "1":"Today",
	 *      "7":"Last 7 Days",
	 *      "30":"Last 30 Days",
	 *      "month":"This Month",
	 *      "lmonth":"Last Month",
	 *      "year":"This Year",
	 *      "fixed":"Fixed Date",
	 *      }
	 *      })
	 *     
	 */
	private $mode;
	
	/**
	 *
	 * @var string @ORM\Column(name="timecreated", type="string", nullable=true)
	 *      @Annotation\Filter({"name":"StripTags"})
	 *      @Annotation\Filter({"name":"StringTrim"})
	 *      @Annotation\Required(false)
	 *      @Annotation\Type("Application\Form\Element\DateRange")
	 *      @Annotation\Options({
	 *      "allow_empty":"true",
	 *      "column-size":"xs-12 col-sm-6 col-md-4",
	 *      "label":"Select Date Range: ",
	 *      })
	 *      @Annotation\Attributes({
	 *      "class":"timecreated",
	 *      })
	 */
	private $timecreated;
	
	/**
	 *
	 * @var Collection @ORM\OneToMany(targetEntity="Agent\Entity\Filter",
	 *      mappedBy="dateFilter",
	 *      cascade={"persist"})
	 */
	private $filters;

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
	 * @return DateFilter
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 *
	 * @return string $mode
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 *
	 * @param string $mode        	
	 *
	 * @return DateFilter
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
		return $this;
	}

	/**
	 *
	 * @return string $timecreated
	 */
	public function getTimecreated()
	{
		return $this->timecreated;
	}

	/**
	 *
	 * @param string $timecreated        	
	 *
	 * @return DateFilter
	 */
	public function setTimecreated($timecreated)
	{
		$this->timecreated = $timecreated;
		return $this;
	}

	/**
	 *
	 * @return Collection $filters
	 */
	public function getFilters()
	{
		return $this->filters;
	}

	/**
	 *
	 * @param Collection $filters        	
	 *
	 * @return DateFilter
	 */
	public function setFilters($filters)
	{
		$this->filters = $filters;
		return $this;
	}

	/**
	 * Add filters to the filter.
	 *
	 * @param Collection $filters        	
	 *
	 * @return void
	 */
	public function addFilters(Collection $filters)
	{
		foreach ( $filters as $filter ) {
			if (!$this->filters->contains($filter)) {
				$this->filters->add($filter);
				$filter->setDateFilter($this);
			}
		}
	}

	/**
	 *
	 * @param Collection $filters        	
	 *
	 * @return DateFilter
	 */
	public function removeFilters(Collection $filters)
	{
		foreach ( $filters as $filter ) {
			if ($this->filters->contains($filter)) {
				$this->filters->removeElement($filter);
				$filter->setDateFilter(null);
			}
		}
		
		return $this;
	}

}

?>