<?php
namespace TenStreet\Hydrator;
use Zend\Stdlib\Hydrator\ClassMethods;

class TenStreetHydrator extends ClassMethods
{

	public function extract ($object)
	{
		$values = parent::extract($object);
		
		unset($values['arrayCopy']);
		
		// remove if null
		return array_filter($values);
	}
}