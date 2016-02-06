<?php

namespace Agent\Entity\Strategy;

use Zend\Stdlib\Hydrator\Strategy\DefaultStrategy;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 *
 * @author arstropica
 *        
 */
class RelationshipStrategy extends DefaultStrategy implements StrategyInterface {

	/**
	 * Converts the given value so that it can be hydrated by the hydrator.
	 *
	 * @param mixed $value
	 *        	The original value.
	 * @return mixed Returns the value that should be hydrated.
	 */
	public function hydrate($value)
	{
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
		return parent::extract($value);
	}

}

?>