<?php

namespace Agent\Entity\Strategy;

use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use Application\Utility\Helper;

/**
 *
 * @author arstropica
 *        
 */
class AgentCriterionValueStrategy extends AbstractCollectionStrategy implements StrategyInterface {

	/**
	 * Converts the given value so that it can be hydrated by the hydrator.
	 *
	 * @param mixed $value
	 *        	The original value.
	 * @return mixed Returns the value that should be hydrated.
	 */
	public function hydrate($value)
	{
		if (Helper::is_json($value)) {
			$value = json_decode($value, true);
		}
		
		return parent::hydrate($value);
	}

	/**
	 * Converts the given value so that it can be extracted by the hydrator.
	 *
	 * @param mixed $value
	 *        	The original value.
	 * @return mixed Returns the value that should be extracted.
	 */
	public function extract($value)
	{
		if (is_array($value)) {
			$value = json_encode($value);
		}
		return parent::extract($value);
	}

}

?>