<?php

namespace WebWorks\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

class PhoneNumberStrategy implements StrategyInterface {

	public function extract($value)
	{
		if ($value === null) {
			return null;
		}
		
		return preg_replace("/[^0-9]/", "", $value);
	}

	public function hydrate($value)
	{
		if ($value === null || $value === '') {
			return null;
		}
		
		$number = preg_replace("/[^0-9]/", "", $value);
		return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '$1-$2-$3', $number);
	}
}