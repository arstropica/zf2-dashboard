<?php

namespace Agent\Elastica\Query;

use Agent;
use Elastica;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

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

	public function __construct(ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
	}

	public function request($location)
	{
		$result = null;
		$location = array_filter($location);
		if (count($location) > 1 || isset($location ['zip'])) {
			try {
				$client = $this->getServiceLocator()
					->get('elastica-client');
				$query = self::getLocalityQuery($location);
				$response = $client->request('usgeodb/locality/_search?query_cache=true', Elastica\Request::GET, $query->toArray());
				if ($response && $response->isOk()) {
					$data = $response->getData();
					if ($data && isset($data ['hits'] ['hits'])) {
						$hits = $data ['hits'] ['hits'];
						$result = reset($hits);
					}
				}
			} catch ( \Exception $e ) {
				return $result;
			}
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
		foreach ( $location as $field => $value ) {
			switch ($field) {
				case 'city' :
					$query->addShould(new Elastica\Query\Match($field, $value));
					break;
				case 'state' :
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