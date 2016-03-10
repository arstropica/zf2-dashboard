<?php

namespace Event\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterAwareInterface;
use Doctrine\ORM\EntityManager;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\ServiceLocatorInterface;

class FilterForm extends Form implements InputFilterAwareInterface {
	
	/**
	 *
	 * @var EntityManager
	 */
	protected $entityManager;
	
	/**
	 *
	 * @var InputFilter
	 */
	protected $inputFilter;
	
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;
	
	protected $url;

	public function __construct(EntityManager $entityManager)
	{
		parent::__construct();
		
		$this->entityManager = $entityManager;
	}

	public function init()
	{
		$this->setAttribute('method', 'GET');
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Select',
				'name' => 'event',
				'required' => false,
				'allow_empty' => true,
				'continue_if_empty' => true,
				'options' => array (
						'label' => 'Filter by Event Type',
						'label_attributes' => array (
								'class' => 'sr-only' 
						),
						'empty_option' => 'Filter by Event',
						'value_options' => array (
								'LeadEvent' => 'Leads',
								'AccountEvent' => 'Account',
								'EmailApiEvent' => 'Email',
								'TenStreetApiEvent' => 'TenStreet',
								'ApiEvent' => 'Options',
								'ErrorEvent' => 'Errors' 
						) 
				),
				'attributes' => array (
						'style' => 'margin: 0 5px' 
				) 
		));
		
		$this->add(array (
				'name' => 'account',
				'type' => 'DoctrineModule\Form\Element\ObjectSelect',
				'required' => false,
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'options' => array (
						'label' => 'Accounts',
						'label_attributes' => array (
								'class' => 'sr-only' 
						),
						'empty_option' => 'Filter by Account',
						'object_manager' => $this->entityManager,
						'target_class' => 'Account\Entity\Account',
						'property' => 'name',
						'is_method' => true,
						'find_method' => array (
								'name' => 'findBy',
								'params' => array (
										'criteria' => array (
												'active' => 1 
										) 
								) 
						) 
				),
				'attributes' => array (
						'id' => 'accountfilter' 
				) 
		));
		
		$this->add(array (
				'name' => 'daterange',
				'required' => false,
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'type' => 'Application\Form\Element\DateRange',
				'options' => array (
						'label' => 'Date Range',
						'label_attributes' => array (
								'class' => 'sr-only' 
						) 
				),
				'attributes' => array (
						'id' => 'daterange' 
				) 
		));
		
		$this->add(array (
				'name' => 'submit',
				'type' => 'Zend\Form\Element\Submit',
				'attributes' => array (
						'type' => 'submit',
						'value' => 'Filter',
						'id' => 'filtersubmit',
						'class' => 'btn btn-secondary' 
				) 
		));
	}

	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
}
