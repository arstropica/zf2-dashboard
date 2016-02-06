<?php

namespace Agent\Entity\Relationship\Query;

use Agent\Entity\Relationship\AbstractQuery;
use Elastica;

/**
 *
 * @author arstropica
 *        
 */
class Contains extends AbstractQuery {
	
	/**
	 *
	 * @var string
	 */
	protected $type = 'contains';
	
	/**
	 *
	 * @var string
	 */
	protected $symbol = '=*';

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
		$method = 'addMust'; // $required ? 'addMust' : 'addShould';
		if (isset($value)) {
			$wildcard = new Elastica\Query\Wildcard('value', "*" . strtolower($value) . "*", $boost);
			$query->{$method}($wildcard);
		}
		return $query;
	}
}

?>