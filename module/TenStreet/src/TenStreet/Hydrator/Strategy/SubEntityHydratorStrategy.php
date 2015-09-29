<?php
namespace TenStreet\Hydrator\Strategy;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use Zend\Stdlib\Hydrator\ClassMethods;

class SubEntityHydratorStrategy implements StrategyInterface
{

	private $subentityHydrator;

	public function __construct (ClassMethods $hydrator)
	{
		$this->subentityHydrator = $hydrator;
	}

	public function extract ($subentity)
	{
		return $this->subentityHydrator->extract($subentity);
	}

	public function hydrate ($value)
	{
		return $this->subentityHydrator->hydrate($value);
	}
}