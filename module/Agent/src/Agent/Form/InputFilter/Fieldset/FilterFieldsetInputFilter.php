<?php

namespace Agent\Form\InputFilter\Fieldset;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Agent\Form\InputFilter\Fieldset\Filters\AccountFilterFieldsetInputFilter;
use Agent\Form\InputFilter\Fieldset\Filters\DateFilterFieldsetInputFilter;

/**
 *
 * @author arstropica
 *        
 */
class FilterFieldsetInputFilter extends InputFilter implements ServiceLocatorAwareInterface {
	
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
		
		$accountFilter = new AccountFilterFieldsetInputFilter($this->getServiceLocator());
		$this->add($accountFilter, 'accountFilter');
		
		$dateFilter = new DateFilterFieldsetInputFilter($this->getServiceLocator());
		$this->add($dateFilter, 'dateFilter');
	}
}

?>