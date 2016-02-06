<?php

namespace Agent\Entity\Relationship\Query;

use Agent\Entity\Relationship\AbstractQuery;
use Elastica;
use Agent\Elastica\Query\LocalityQuery;

/**
 *
 * @author arstropica
 *        
 */
class Location extends AbstractQuery {
	
	/**
	 *
	 * @var string
	 */
	protected $type = 'location';
	
	/**
	 *
	 * @var string
	 */
	protected $symbol = 'locale';

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
	public function getQuery($values = null, $query = null, $required = null, $boost = null)
	{
		$fields = array_flip([ 
				'city',
				'state',
				'zip' 
		]);
		$location = array_intersect_key(array_filter($values), $fields);
		$criterion = $this->getCriterion();
		$method = 'addMust'; // $required ? 'addMust' : 'addShould';
		
		$distance = empty($values ['distance']) ? false : $values ['distance'];
		
		if ($criterion && $location) {
			if (!$distance) {
				$query = parent::getQuery($values, $query, $required, $boost);
				$attribute = $criterion->getAttribute();
				if ($attribute) {
					$attributeDesc = $attribute->getAttributeDesc();
					if ($attributeDesc) {
						$token = strtolower(trim(current(explode(' ', $attributeDesc))));
						if ($token && isset($location [$token])) {
							$value = $location [$token];
							if (isset($value)) {
								$query->{$method}(new Elastica\Query\Match('value', $value));
							}
						}
					}
				}
			} else {
				try {
					$localityQuery = new LocalityQuery($criterion->getServiceLocator());
					$locality = $localityQuery->request($location);
					if ($locality) {
						list ( $latlon ['lat'], $latlon ['lon'] ) = $locality ['_source'] ['latlon'];
						$query = new Elastica\Query\GeoDistance('locality', $latlon, $distance);
					}
				} catch ( \Exception $e ) {
					//
				}
			}
		}
		return $query;
	}

}

?>