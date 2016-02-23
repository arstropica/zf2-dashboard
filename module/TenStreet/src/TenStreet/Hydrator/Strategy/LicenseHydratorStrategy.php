<?php
namespace TenStreet\Hydrator\Strategy;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use Zend\Stdlib\Hydrator\ClassMethods;

class LicenseHydratorStrategy implements StrategyInterface
{

	private $licenseHydrator;

	public function __construct (ClassMethods $hydrator)
	{
		$this->licenseHydrator = $hydrator;
	}

	public function extract ($licenses)
	{
		$data = ['License' => []];
		
		foreach ($licenses as $license) {
			$data['License'][] = $this->licenseHydrator->extract($license);
		}
		
		return $data;
	}

	public function hydrate ($value)
	{
		throw new \RuntimeException('Hydration is not supported');
	}
}