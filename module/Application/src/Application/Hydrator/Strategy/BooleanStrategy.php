<?php
namespace Application\Hydrator\Strategy;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class BooleanStrategy implements StrategyInterface
{

	public function extract ($value)
	{
		if ($value === null) {
			return null;
		}
		
		if (! is_bool($value) && ! in_array($value, [
				"True",
				"False"
		])) {
			throw new \RuntimeException('$value is expected to be boolean or True/False.');
		}
		
		return $value === true || $value === "True" ? 1 : 0;
	}

	public function hydrate ($value)
	{
		if ($value === null || $value === '') {
			return null;
		}
		
		if (! in_array($value, [
				0,
				1
		])) {
			throw new \RuntimeException('$value is expected to be 0 or 1.');
		}
		
		return $value == 1 ? "True" : "False";
	}
}