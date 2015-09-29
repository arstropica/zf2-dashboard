<?php
namespace TenStreet\Hydrator\Strategy;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class ContactDataAttributesStrategy implements StrategyInterface
{

	public function extract ($value)
	{
		if (! is_array($value)) {
			throw new \RuntimeException('$value is expected to be array.');
		}
		
		return array(
				'@attributes' => $value
		);
	}

	public function hydrate ($value)
	{
		if (! is_array($value)) {
			throw new \RuntimeException('$value is expected to be array.');
		}
		
		return isset($value['@attributes']) ? $value['@attributes'] : $value;
	}
}