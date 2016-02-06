<?php

namespace WebWorks\Hydrator\Strategy;

use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use Zend\Stdlib\Hydrator\ClassMethods;

class DisplayFieldHydratorStrategy implements StrategyInterface {
	
	private $displayFieldHydrator;

	public function __construct(ClassMethods $hydrator)
	{
		$this->displayFieldHydrator = $hydrator;
	}

	public function extract($displayFields)
	{
		$data = [ 
				'DisplayField' => [ ] 
		];
		
		foreach ( $displayFields as $displayField ) {
			$data ['DisplayField'] [] = $this->displayFieldHydrator->extract($displayField);
		}
		
		return $data;
	}

	public function hydrate($value)
	{
		throw new \RuntimeException('Hydration is not supported');
	}
}