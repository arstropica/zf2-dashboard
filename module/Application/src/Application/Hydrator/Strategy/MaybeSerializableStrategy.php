<?php
namespace Application\Hydrator\Strategy;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 *
 * @author arstropica
 *        
 */
class MaybeSerializableStrategy implements StrategyInterface
{

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface::extract()
	 *
	 */
	public function extract ($value)
	{
		if ($this->isSerialized($value)) {
			return unserialize($value);
		}
		return $value;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface::hydrate()
	 *
	 */
	public function hydrate ($value)
	{
		if ($this->isSerializable($value)) {
			return serialize($value);
		}
		return $value;
	}

	function isSerializable ($value)
	{
		try {
			serialize($value);
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	public function isSerialized ($value, &$result = null)
	{
		// Bit of a give away this one
		if (! is_string($value)) {
			return false;
		}
		// Serialized false, return true. unserialize() returns false on an
		// invalid string or it could return false if the string is serialized
		// false, eliminate that possibility.
		if ($value === 'b:0;') {
			$result = false;
			return true;
		}
		$length = strlen($value);
		$end = '';
		switch ($value[0]) {
			case 's':
				if ($value[$length - 2] !== '"') {
					return false;
				}
			case 'b':
			case 'i':
			case 'd':
				// This looks odd but it is quicker than isset()ing
				$end .= ';';
			case 'a':
			case 'O':
				$end .= '}';
				if ($value[1] !== ':') {
					return false;
				}
				switch ($value[2]) {
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
						break;
					default:
						return false;
				}
			case 'N':
				$end .= ';';
				if ($value[$length - 1] !== $end[0]) {
					return false;
				}
				break;
			default:
				return false;
		}
		if (($result = @unserialize($value)) === false) {
			$result = null;
			return false;
		}
		return true;
	}
}
