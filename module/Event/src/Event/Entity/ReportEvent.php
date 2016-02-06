<?php

namespace Event\Entity;

use Doctrine\ORM\Mapping as ORM;
use Event\Entity\Event;
use Report\Entity\Report;

/**
 * ReportEvent
 *
 * @ORM\Table(name="events_report")
 * @ORM\Entity
 */
class ReportEvent {
	
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;
	
	/**
	 *
	 * @var \Report\Entity\Report @ORM\ManyToOne(
	 *      targetEntity="Report\Entity\Report",
	 *      inversedBy="events",
	 *      fetch="EXTRA_LAZY",
	 *      cascade={"merge", "persist"},
	 *      )
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(
	 *      name="report_id",
	 *      referencedColumnName="id",
	 *      nullable=false,
	 *      )
	 *      })
	 */
	private $report;
	
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
	 * Set report
	 *
	 * @param \Report\Entity\Report $report        	
	 *
	 * @return ReportEvent
	 */
	public function setReport(\Report\Entity\Report $report = null)
	{
		$this->report = $report;
		
		return $this;
	}

	/**
	 * Get report
	 *
	 * @return \Report\Entity\Report
	 */
	public function getReport()
	{
		return $this->report;
	}

	/**
	 *
	 * @return \Event\Entity\Event
	 */
	public function getEvent()
	{
		return $this->event;
	}

	/**
	 *
	 * @param \Event\Entity\Event $event        	
	 */
	public function setEvent($event)
	{
		$this->event = $event;
		
		return $this;
	}

	/**
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @param int $id        	
	 *
	 * @return AccountApiEvent
	 */
	public function setId($id)
	{
		$this->id = $id;
		
		return $this;
	}
}
