<?php

namespace Agent\Entity\Relationship\Query;

use Agent\Entity\Relationship\AbstractQuery;
use Elastica;

/**
 *
 * @author arstropica
 *        
 */
class Multiple extends AbstractQuery {
	
	/**
	 *
	 * @var string
	 */
	protected $type = 'multiple';
	
	/**
	 *
	 * @var string
	 */
	protected $symbol = '[]';

	/**
	 *
	 * @param \Agent\Entity\AgentCriterion $criterion        	
	 */
	public function __construct($criterion = null)
	{
		parent::__construct($criterion);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Agent\Entity\Relationship::getQuery()
	 */
	public function getQuery($value = null, $query = null, $required = null, $boost = null)
	{
		$query = parent::getQuery($value, $query, $required, $boost);
		$method = $required ? 'addShould' : 'addShould';
		if (isset($value) && is_array($value)) {
			foreach ( $value as $v ) {
				$query->$method(new Elastica\Query\Match('value', $v));
			}
		}
		return $query;
	}
}

?>