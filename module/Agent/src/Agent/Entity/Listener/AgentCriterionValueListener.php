<?php

namespace Agent\Entity\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Agent\Entity\AgentCriterionValue;
use Application\Utility\Helper;

/**
 *
 * @author arstropica
 *        
 */
class AgentCriterionValueListener {

	/**
	 * @ORM\PostLoad
	 *
	 * @param AgentCriterionValue $agentCriterionValue        	
	 * @param LifecycleEventArgs $event        	
	 */
	public function postLoad(AgentCriterionValue $agentCriterionValue, LifecycleEventArgs $event)
	{
		$fValue = null;
		$value = $agentCriterionValue->getValue();
		if ($value && Helper::is_serialized($value, $fValue)) {
			$agentCriterionValue->setValue($fValue);
		}
	}

	/**
	 * Occurs before Entity persistence
	 *
	 * @ORM\PrePersist
	 *
	 * @param AgentCriterionValue $agentCriterionValue        	
	 * @param LifecycleEventArgs $event        	
	 */
	public function prePersist(AgentCriterionValue $agentCriterionValue, LifecycleEventArgs $event)
	{
		$input = false;
		$fValue = null;
		$criterion = $agentCriterionValue->getCriterion();
		if ($criterion) {
			$relationship = $criterion->getRelationship();
			if ($relationship) {
				$input = $relationship->getInput();
			}
		}
		
		if ($input) {
			$method = 'get' . ucwords($input);
			if (method_exists($agentCriterionValue, $method)) {
				switch ($input) {
					case 'boolean' :
					case 'string' :
					case 'daterange' :
					case 'range' :
						$fValue = $agentCriterionValue->{$method}();
						break;
					case 'location' :
					case 'multiple' :
						$fValue = is_array($agentCriterionValue->{$method}()) ? serialize($agentCriterionValue->{$method}()) : $agentCriterionValue->{$method}();
						break;
				}
				$agentCriterionValue->setValue($fValue);
			}
		}
	}

	/**
	 * Occurs before Entity persistence
	 *
	 * @ORM\PreUpdate
	 *
	 * @param AgentCriterionValue $agentCriterionValue        	
	 * @param LifecycleEventArgs $event        	
	 */
	public function preUpdate(AgentCriterionValue $agentCriterionValue, LifecycleEventArgs $event)
	{
		$input = false;
		$fValue = null;
		$criterion = $agentCriterionValue->getCriterion();
		if ($criterion) {
			$relationship = $criterion->getRelationship();
			if ($relationship) {
				$input = $relationship->getInput();
			}
		}
		
		if ($input) {
			$method = 'get' . ucwords($input);
			if (method_exists($agentCriterionValue, $method)) {
				switch ($input) {
					case 'boolean' :
					case 'string' :
					case 'daterange' :
					case 'range' :
						$fValue = $agentCriterionValue->{$method}();
						break;
					case 'location' :
					case 'multiple' :
						$fValue = is_array($agentCriterionValue->{$method}()) ? serialize($agentCriterionValue->{$method}()) : $agentCriterionValue->{$method}();
						break;
				}
				$agentCriterionValue->setValue($fValue);
			}
		}
	}

}

?>