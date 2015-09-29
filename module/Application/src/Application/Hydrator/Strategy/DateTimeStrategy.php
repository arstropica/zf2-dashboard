<?php
namespace Application\Hydrator\Strategy;
use Zend\Stdlib\Hydrator\Strategy\DefaultStrategy;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use Zend\Stdlib\DateTime;

class DateTimeStrategy extends DefaultStrategy implements StrategyInterface
{

	/**
	 * Convert a string value into a DateTime object
	 */
	public function hydrate ($value)
	{
		if (empty($value) || $value === "0000-00-00 00:00:00") {
			$value = null;
		} elseif (is_string($value) && "" === $value) {
			$value = null;
		} elseif (is_string($value)) {
			return new \DateTime(date('Y-m-d\TH:i:s', strtotime($value)));
		}
		return $value;
	}

	/**
	 * Convert a DateTime object into a string value
	 */
	public function extract ($datetime)
	{
		$value = $datetime;
		if ($datetime instanceof \DateTime || $datetime instanceof DateTime) {
			$value = $datetime->format('Y-m-d\TH:i:s');
		}
		return $value;
	}
}