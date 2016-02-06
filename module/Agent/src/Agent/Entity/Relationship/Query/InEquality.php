<?php

namespace Agent\Entity\Relationship\Query;

use Agent\Entity\Relationship\AbstractQuery;
use Elastica;

/**
 *
 * @author arstropica
 *        
 */
class InEquality extends AbstractQuery {
	
	/**
	 *
	 * @var string
	 */
	protected $type = 'inequality';
	
	/**
	 *
	 * @var string
	 */
	protected $symbol = '!=';

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
		$method = $required ? 'addMustNot' : 'addMustNot';
		if (isset($value)) {
			$query->{$method}(new Elastica\Query\Match('value', $value));
		}
		return $query;
	}
}

?>