<?php

namespace Agent\Form\InputFilter\Fieldset;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Agent\Form\InputFilter\Collection\CriteriaInputFilter;

/**
 *
 * @author arstropica
 *        
 */
class AgentFieldsetInputFilter extends InputFilter implements ServiceLocatorAwareInterface {
	
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
				'name' => 'updated',
				'required' => true,
				'filters' => array (
						array (
								'name' => 'StripTags' 
						),
						array (
								'name' => 'StringTrim' 
						) 
				) 
		)));
		
		$this->add($factory->createInput(array (
				'name' => 'account',
				'required' => false 
		)));
		
		$filtersFilter = new FilterFieldsetInputFilter($this->getServiceLocator());
		$this->add($filtersFilter, 'filter');
		
		$criteriaFilter = new CriteriaInputFilter();
		$criteriaFilter->setInputFilter(new CriterionFieldsetInputFilter($this->getServiceLocator()));
		$this->add($criteriaFilter, 'criteria');
	}
}

?>