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
								if (is_array($value)) {
									$statesQuery = new \Agent\Elastica\Query\BoolQuery();
									foreach ( $value as $_value ) {
										$state_full_name = $this->getStateNames($_value);
										if ($state_full_name) {
											$statesQuery->addShould(new Elastica\Query\Match('value', $state_full_name));
										}
										$statesQuery->addShould(new Elastica\Query\Match('value', $_value));
									}
									$query->{$method}($statesQuery);
								} else {
									$state_full_name = $this->getStateNames($value);
									if ($state_full_name) {
										$query->{$method}(new Elastica\Query\Match('value', $state_full_name));
									}
									$query->{$method}(new Elastica\Query\Match('value', $value));
								}
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
						$query = new Elastica\Query\GeoDistance('locality', $latlon, $distance . "mi");
					}
				} catch ( \Exception $e ) {
					//
				}
			}
		}
		return $query;
	}

	private function getStateNames($state, $abbrev = false)
	{
		$us_state_abbrevs_names = array (
				'AL' => 'ALABAMA',
				'AK' => 'ALASKA',
				'AS' => 'AMERICAN SAMOA',
				'AZ' => 'ARIZONA',
				'AR' => 'ARKANSAS',
				'CA' => 'CALIFORNIA',
				'CO' => 'COLORADO',
				'CT' => 'CONNECTICUT',
				'DE' => 'DELAWARE',
				'DC' => 'DISTRICT OF COLUMBIA',
				'FM' => 'FEDERATED STATES OF MICRONESIA',
				'FL' => 'FLORIDA',
				'GA' => 'GEORGIA',
				'GU' => 'GUAM GU',
				'HI' => 'HAWAII',
				'ID' => 'IDAHO',
				'IL' => 'ILLINOIS',
				'IN' => 'INDIANA',
				'IA' => 'IOWA',
				'KS' => 'KANSAS',
				'KY' => 'KENTUCKY',
				'LA' => 'LOUISIANA',
				'ME' => 'MAINE',
				'MH' => 'MARSHALL ISLANDS',
				'MD' => 'MARYLAND',
				'MA' => 'MASSACHUSETTS',
				'MI' => 'MICHIGAN',
				'MN' => 'MINNESOTA',
				'MS' => 'MISSISSIPPI',
				'MO' => 'MISSOURI',
				'MT' => 'MONTANA',
				'NE' => 'NEBRASKA',
				'NV' => 'NEVADA',
				'NH' => 'NEW HAMPSHIRE',
				'NJ' => 'NEW JERSEY',
				'NM' => 'NEW MEXICO',
				'NY' => 'NEW YORK',
				'NC' => 'NORTH CAROLINA',
				'ND' => 'NORTH DAKOTA',
				'MP' => 'NORTHERN MARIANA ISLANDS',
				'OH' => 'OHIO',
				'OK' => 'OKLAHOMA',
				'OR' => 'OREGON',
				'PW' => 'PALAU',
				'PA' => 'PENNSYLVANIA',
				'PR' => 'PUERTO RICO',
				'RI' => 'RHODE ISLAND',
				'SC' => 'SOUTH CAROLINA',
				'SD' => 'SOUTH DAKOTA',
				'TN' => 'TENNESSEE',
				'TX' => 'TEXAS',
				'UT' => 'UTAH',
				'VT' => 'VERMONT',
				'VI' => 'VIRGIN ISLANDS',
				'VA' => 'VIRGINIA',
				'WA' => 'WASHINGTON',
				'WV' => 'WEST VIRGINIA',
				'WI' => 'WISCONSIN',
				'WY' => 'WYOMING',
				'AE' => 'ARMED FORCES AFRICA \ CANADA \ EUROPE \ MIDDLE EAST',
				'AA' => 'ARMED FORCES AMERICA (EXCEPT CANADA)',
				'AP' => 'ARMED FORCES PACIFIC' 
		);
		
		$result = $abbrev ? array_search($state, $us_state_abbrevs_names) : (isset($us_state_abbrevs_names [$state]) ? $us_state_abbrevs_names [$state] : false);
		return $result && !$abbrev ? strtolower($result) : $result;
	}

}

?>