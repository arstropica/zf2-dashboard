<?php

namespace Report\Entity;

use Lead\Entity\Lead;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Application\Provider\ObjectManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @author arstropica
 *        
 */
class Result implements ServiceLocatorAwareInterface, ObjectManagerAwareInterface {
	
	use ServiceLocatorAwareTrait, ObjectManagerAwareTrait;
	
	/**
	 *
	 * @var Lead
	 */
	private $lead;
	
	/**
	 *
	 * @var float
	 */
	private $_score;
	
	/**
	 *
	 * @var Collection
	 */
	private $reports;

	public function __construct(ServiceLocatorInterface $serviceLocator = null)
	{
		if ($serviceLocator) {
			$this->setServiceLocator($serviceLocator);
		}
		$this->reports = new ArrayCollection();
	}

	/**
	 *
	 * @return Lead
	 */
	public function getLead()
	{
		$lead = $this->lead;
		if ($lead instanceof Lead && $lead->getProxy()) {
			$lead = $this->_hydrate($lead);
			$this->lead = $lead;
		}
		return $lead;
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
	 * @return number $_score
	 */
	public function getScore()
	{
		return $this->_score;
	}

	/**
	 *
	 * @param float $_score        	
	 *
	 * @return Result
	 */
	public function setScore($_score)
	{
		$this->_score = $_score;
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

	/**
	 *
	 * @param Lead $proxy        	
	 *
	 * @return \Lead\Entity\Lead
	 */
	private function _hydrate(Lead $proxy)
	{
		$lead = null;
		$reports = $this->getReports();
		$objectManager = $this->getObjectManager();
		if ($objectManager) {
			$leadRepo = $objectManager->getRepository('Lead\Entity\Lead');
			if ($proxy && $proxy->getProxy() && (($id = $proxy->getId()) == true)) {
				$lead = $leadRepo->findLead($id);
				if ($lead) {
					$lead->setProxy(false);
				}
			} elseif (!$proxy->getProxy()) {
				$lead = $proxy;
			}
		}
		return $lead;
	}
}

?>