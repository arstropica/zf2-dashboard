<?php

namespace Agent\Form\InputFilter\Fieldset;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;

class CriterionFieldsetInputFilter extends InputFilter implements ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait;

	public function __construct(ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
		
		$this->init();
	}

	public function init()
	{
		$factory = new InputFactory();
		
		$this->add($factory->createInput(array (
				'name' => 'id',
				'required' => false 
		)));
		
		$this->add($factory->createInput(array (
				'name' => 'attribute',
				'required' => true,
				'validators' => array (
						array (
								'name' => 'NotEmpty' 
						) 
				) 
		)));
		
		$this->add($factory->createInput(array (
				'name' => 'relationship',
				'required' => true,
				'validators' => array (
						array (
								'name' => 'NotEmpty' 
						) 
				) 
		)));
		
		$this->add($factory->createInput(array (
				'name' => 'weight',
				'required' => false 
		)));
		
		$this->add($factory->createInput(array (
				'name' => 'required',
				'required' => true 
		)));
		
		// Add the value fieldset filter
		$valuesFilter = new ValueFieldsetInputFilter($this->getServiceLocator());
		$this->add($valuesFilter, 'value');
	}

}

?>