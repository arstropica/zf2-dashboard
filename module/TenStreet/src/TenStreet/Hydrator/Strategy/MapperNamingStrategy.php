<?php
namespace TenStreet\Hydrator\Strategy;
use Zend\Stdlib\Hydrator\NamingStrategy\NamingStrategyInterface;

class MapperNamingStrategy implements NamingStrategyInterface
{

	private $hydrateMap;

	public function __construct (array $hydrateMap = [])
	{
		$this->hydrateMap = $hydrateMap;
	}

	public function hydrate ($name)
	{
		if (($key = array_search($name, $this->hydrateMap)) !== false) {
			return $key;
		}
		
		return $name;
	}

	public function extract ($name)
	{
		if (array_key_exists($name, $this->hydrateMap)) {
			return $this->hydrateMap[$name];
		}
		
		return $name;
	}
}