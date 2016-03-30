<?php

namespace Agent\Elastica\Query;

use Agent;
use Elastica;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfSnapGeoip\Entity\Record;
use ZfSnapGeoip\Service\Geoip;
use Application\Utility\Helper;

/**
 *
 * @author arstropica
 *        
 */
class LocalityQuery implements ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait;

	/**
	 *
	 * @var Elastica\Query
	 */
	protected $query;

	/**
	 *
	 * @var Geoip
	 */
	protected static $geo;

	public function __construct(ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
		if (!isset(self::$geo)) {
			self::$geo = $this->getServiceLocator()
				->get('geoip');
		}
	}

	public function request($location)
	{
		$result = null;
		
		// Fix Truncated ZIP
		if (isset($location['zip']) && strlen($location['zip']) < 5) {
			$location['zip'] = str_pad($location['zip'], 5, '0', STR_PAD_LEFT);
		}
		
		if ((isset($location['city']) && count($location) > 1) || isset($location['zip']) || isset($location['locality'])) {
			if (isset($location['ipaddress'], $location['phone'])) {
				foreach ( [ 
						'phone',
						'ipaddress' 
				] as $deduce ) {
					$data = $location;
					unset($data[$deduce]);
					$result = $this->_request($data);
					if ($result) {
						break 1;
					}
				}
			} else {
				$result = $this->_request($location);
			}
			// Try without ZIP
			if (!$result && isset($location['zip']) && count($location) > 1) {
				unset($location['zip']);
				$result = $this->request($location);
			}
		}
		return $result;
	}

	private function _request($location)
	{
		$result = null;
		try {
			$client = $this->getServiceLocator()
				->get('elastica-client');
			$query = self::getLocalityQuery($location);
			$response = $client->request('usgeodb/locality/_search?query_cache=true', Elastica\Request::GET, $query->toArray());
			if ($response && $response->isOk()) {
				$data = $response->getData();
				if ($data && isset($data['hits']['hits'])) {
					$hits = $data['hits']['hits'];
					$result = reset($hits);
				}
			}
		} catch ( \Exception $e ) {
			return $result;
		}
		return $result;
	}

	/**
	 *
	 * @param array $location        	
	 *
	 * @return Elastica\Query $localityQuery
	 */
	public static function getLocalityQuery($location)
	{
		$query = new Agent\Elastica\Query\BoolQuery();
		$method = 'addMust';
		if (!isset($location['state']) && !isset($location['zip']) && !isset($location['locality'])) {
			foreach ( [ 
					'phone',
					'ipaddress' 
			] as $field ) {
				if (isset($location[$field]) && empty($location['state'])) {
					switch ($field) {
						case 'ipaddress' :
							$geo = self::$geo;
							$loc = $geo->getRecord($location['ipaddress']);
							if ($loc instanceof Record) {
								$state = $loc->getRegion();
								if ($state) {
									$location['state'] = $state;
								}
							}
							break;
						case 'phone' :
							$phone = Helper::parse_phonenumber($location['phone'], 'array');
							if ($phone) {
								$state = Helper::area_code_to_state($phone[0]);
								if ($state) {
									$location['state'] = $state;
								}
							}
							break;
					}
				}
			}
		}
		foreach ( $location as $field => $value ) {
			switch ($field) {
				case 'locality' :
					if (!isset($location['zip'])) {
						$fields = [ 
								'latitude',
								'longitude' 
						];
						$values = is_array($value) ? $value : explode(",", $value);
						$latlon = count($values) == 2 ? array_combine($fields, $values) : false;
						if ($latlon) {
							$path = "location";
							$nested = new Elastica\Query\Nested();
							$nested->setPath($path);
							$bool = new Elastica\Query\BoolQuery();
							foreach ( $latlon as $dim => $coord ) {
								$bool->addMust(new Elastica\Query\Match("{$path}.{$dim}", $coord));
							}
							$nested->setQuery($bool);
							$query->addMust($nested);
						}
					}
					break;
				case 'city' :
					if (!isset($location['locality'])) {
						$query->addShould(new Elastica\Query\Match($field, $value));
					}
					break;
				case 'state' :
					if (!isset($location['locality'])) {
						$fields = [ 
								'state.abbrev',
								'state.full' 
						];
						$values = is_array($value) ? $value : [ 
								$value 
						];
						foreach ( $values as $state ) {
							$querystring = new Elastica\Query\QueryString($state);
							$querystring->setFields($fields);
							$nested = new Elastica\Query\Nested();
							$nested->setQuery($querystring);
							$nested->setPath($field);
							if (count($values) > 1) {
								$query->addShould($nested);
							} else {
								$query->addMust($nested);
							}
						}
					}
					break;
				case 'zip' :
					$query->{$method}(new Elastica\Query\Match($field, $value));
					break;
			}
		}
		$localityQuery = new Elastica\Query($query);
		$localityQuery->setSize(1);
		return $localityQuery;
	}

	public function toArray()
	{
		if ($this->query) {
			return $this->query->toArray();
		}
		return [ ];
	}
}

?>