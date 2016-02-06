<?php

namespace Agent\Entity\Relationship\Query;

use Agent\Entity\Relationship\AbstractQuery;
use Elastica;

/**
 *
 * @author arstropica
 *        
 */
class Range extends AbstractQuery {
	
	/**
	 *
	 * @var string
	 */
	protected $type = 'range';
	
	/**
	 *
	 * @var string
	 */
	protected $symbol = '<>';

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
		$from = $to = null;
		if ($value && is_array($value) && count($value) == 2) {
			@list ( $from, $to ) = $value;
		}
		$query = parent::getQuery(null, $query, $required, $boost);
		$method = 'addMust'; // $required ? 'addMust' : 'addShould';
		if (isset($from, $to)) {
			$range = new Elastica\Query\Range();
			$range->addField($this->getCriterion()
				->getTypeField(), [ 
					'gte' => $from,
					'lte' => $to 
			]);
			$query->{$method}($range);
		}
		return $query;
	}
}

?>