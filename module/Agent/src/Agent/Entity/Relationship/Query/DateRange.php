<?php

namespace Agent\Entity\Relationship\Query;

use Agent\Entity\Relationship\AbstractQuery;
use Elastica;

/**
 *
 * @author arstropica
 *        
 */
class DateRange extends AbstractQuery {
	
	/**
	 *
	 * @var string
	 */
	protected $type = 'daterange';
	
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
		$query = parent::getQuery($value, $query, $required, $boost);
		$method = 'addMust'; // $required ? 'addMust' : 'addShould';
		if (isset($value) && is_array($value) && count($value) == 2) {
			$range = new Elastica\Query\Range();
			$field = $this->getCriterion()
				->getTypeField();
			switch ($field) {
				case '_date' :
					foreach ( $value as &$date ) {
						$time = strtotime($date);
						if ($time) {
							$date = date('Y-m-d\TH:i:s', $time);
						} else {
							$date = date('Y-m-d\TH:i:s', -9999999999);
						}
					}
					break;
			}
			@list ( $from, $to ) = $value;
			if (isset($from, $to)) {
				$range->addField($field, [ 
						'gt' => $from,
						'lt' => $to 
				]);
				$query->{$method}($range);
			}
		}
		return $query;
	}
}

?>