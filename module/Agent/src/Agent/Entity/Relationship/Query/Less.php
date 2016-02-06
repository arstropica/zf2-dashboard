<?php

namespace Agent\Entity\Relationship\Query;

use Agent\Entity\Relationship\AbstractQuery;
use Elastica;

/**
 *
 * @author arstropica
 *        
 */
class Less extends AbstractQuery {
	
	/**
	 *
	 * @var string
	 */
	protected $type = 'less';
	
	/**
	 *
	 * @var string
	 */
	protected $symbol = '<';

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
			$range = new Elastica\Query\Range();
			$field = $this->getCriterion()
				->getTypeField();
			switch ($field) {
				case '_date' :
					$time = strtotime($value);
					if ($time) {
						$value = date('Y-m-d\TH:i:s', $time);
					} else {
						$value = date('Y-m-d\TH:i:s', -9999999999);
					}
					break;
			}
			$range->addField($field, [ 
					'lte' => $value 
			]);
			$query->{$method}($range);
		}
		return $query;
	}
}

?>