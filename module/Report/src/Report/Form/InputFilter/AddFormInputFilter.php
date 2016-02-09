<?php

namespace Report\Form\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Agent\Form\InputFilter\Fieldset\AgentFieldsetInputFilter;

/**
 *
 * @author arstropica
 *        
 */
class AddFormInputFilter extends InputFilter implements ServiceLocatorAwareInterface {
	
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
				'required' => false,
				'filters' => array (
						array (
								'name' => 'Int' 
						) 
				) 
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
				'name' => 'name',
				'required' => true,
				'filters' => array (
						array (
								'name' => 'StringTrim' 
						) 
				),
				'validators' => array (
						array (
								'name' => 'NotEmpty' 
						) 
				) 
		)));
		
		$this->add($factory->createInput(array (
				'name' => 'account',
				'required' => false,
				'filters' => array (
						array (
								'name' => 'Int' 
						) 
				) 
		)));
		
		$this->add(new AgentFieldsetInputFilter($this->getServiceLocator()), 'agent');
	}
}

?>