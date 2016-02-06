<?php

namespace Agent\Entity\Relationship\Query;

use Agent\Entity\Relationship\AbstractQuery;
use Elastica;

/**
 *
 * @author arstropica
 *        
 */
class Boolean extends AbstractQuery {
	
	/**
	 *
	 * @var string
	 */
	protected $type = 'boolean';
	
	/**
	 *
	 * @var string
	 */
	protected $symbol = 'true/false';

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
			$bool = (bool) $value;
			switch ($bool) {
				case true :
					$query->{$method}(new Elastica\Query\Terms('value', [ 
							'yes',
							'true',
							'1' 
					]));
					break;
				case false :
					$query->{$method}(new Elastica\Query\Terms('value', [ 
							'no',
							'false',
							'0' 
					]));
					break;
			}
		}
		return $query;
	}
}

?>