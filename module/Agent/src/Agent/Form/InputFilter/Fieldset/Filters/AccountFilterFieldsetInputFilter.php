<?php

namespace Agent\Form\InputFilter\Fieldset\Filters;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Application\Form\Validator\Proxy;
use Zend\Validator\NotEmpty;

/**
 *
 * @author arstropica
 *        
 */
class AccountFilterFieldsetInputFilter extends InputFilter implements ServiceLocatorAwareInterface {
	
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
				'name' => 'mode',
				'required' => false,
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
				'required' => true,
				'filters' => array (
						array (
								'name' => 'StripTags' 
						),
						array (
								'name' => 'StringTrim' 
						) 
				),
				'validators' => $this->_getValidatorSpecification() 
		)));
	
	}

	public function isValid()
	{
		$fields = [ 
				'id',
				'mode' 
		];
		
		$mode = $this->getMode();
		
		if ($mode == 'account') {
			$fields [] = 'account';
		}
		
		$this->setValidationGroup($fields);
		
		return parent::isValid();
	}

	/**
	 *
	 * @return string $mode
	 */
	public function getMode()
	{
		return $this->getValue('mode');
	}

	protected function _getValidatorSpecification()
	{
		$self = $this;
		
		$notEmptyValidator = new NotEmpty();
		$condition = function () use($self) {
			$mode = $self->get('mode')
				->getValue();
			return $mode =='account' ? true : false;
		};
		
		$proxyValidator = new Proxy($notEmptyValidator, $condition);
		
		return array (
				array (
						'name' => 'Zend\Validator\NotEmpty',
						'options' => array (
								'type' => 'null' 
						) 
				),
				$proxyValidator 
		);
	}
}

?>