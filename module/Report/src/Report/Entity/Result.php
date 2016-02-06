<?php

namespace Report\Entity;

use Lead\Entity\Lead;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @author arstropica
 *        
 */
class Result {
	
	/**
	 *
	 * @var Lead
	 */
	private $lead;
	
	/**
	 *
	 * @var float
	 */
	private $score;
	
	/**
	 *
	 * @var Collection
	 */
	private $reports;

	public function __construct()
	{
		$this->reports = new ArrayCollection();
	}

	/**
	 *
	 * @return Lead
	 */
	public function getLead()
	{
		return $this->lead;
	}

	/**
	 *
	 * @param \Lead\Entity\Lead $lead        	
	 *
	 * @return Result
	 */
	public function setLead($lead)
	{
		$this->lead = $lead;
		return $this;
	}

	/**
	 *
	 * @return number $score
	 */
	public function getScore()
	{
		return $this->score;
	}

	/**
	 *
	 * @param float $score        	
	 *
	 * @return Result
	 */
	public function setScore($score)
	{
		$this->score = $score;
		return $this;
	}

	/**
	 *
	 * @return Collection|Array $reports
	 */
	public function getReports($ac = false)
	{
		return $ac ? $this->reports : $this->reports->getValues();
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\Collection $reports        	
	 *
	 * @return Result
	 */
	public function setReports($reports)
	{
		$this->reports = $reports;
		return $this;
	}

	/**
	 *
	 * @param Collection|Array $reports        	
	 *
	 */
	public function addReports($reports)
	{
		foreach ( $reports as $report ) {
			if (!$this->reports->contains($report)) {
				$this->reports->add($report);
			}
		}
	}

	/**
	 *
	 * @param Collection|Array $reports        	
	 *
	 */
	public function removeReports($reports)
	{
		foreach ( $reports as $report ) {
			if ($this->reports->contains($report)) {
				$this->reports->removeElement($report);
			}
		}
	}

}

?>