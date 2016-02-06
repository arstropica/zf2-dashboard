<?php

namespace Report\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Doctrine\ORM\EntityManager;

class ListForm extends Form implements InputFilterAwareInterface {
	
	/**
	 *
	 * @var EntityManager
	 */
	protected $entityManager;

	public function __construct(EntityManager $entityManager)
	{
		parent::__construct();
		
		$this->entityManager = $entityManager;
	}

	public function init()
	{
		$this->add(array (
				'type' => 'Zend\Form\Element\Csrf',
				'name' => 'csrf',
				'attributes' => array (
						'id' => 'csrf' 
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
						'empty_option' => 'Choose Account',
						'object_manager' => $this->entityManager,
						'target_class' => 'Account\Entity\Account',
						'property' => 'name',
						'is_method' => true,
						'find_method' => array (
								'name' => 'getAccounts' 
						) 
				),
				'attributes' => array (
						'id' => 'accountfilter',
						'style' => 'margin: 0 5px' 
				) 
		));
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Select',
				'name' => 'bulk_action',
				'required' => true,
				'allow_empty' => false,
				'continue_if_empty' => false,
				'options' => array (
						'label' => 'Choose Action',
						'label_attributes' => array (
								'class' => 'sr-only' 
						),
						'empty_option' => 'Choose Bulk Action',
						'value_options' => array (
								'assign' => 'Assign',
								'unassign' => 'Unassign',
								'delete' => 'Delete' 
						) 
				),
				'attributes' => array (
						'style' => 'margin: 0 5px' 
				) 
		));
		
		$this->add(array (
				'name' => 'submit',
				'type' => 'Zend\Form\Element\Submit',
				'options' => array (),
				'attributes' => array (
						'type' => 'submit',
						'value' => 'Apply',
						'id' => 'listsubmit',
						'class' => 'btn btn-primary',
						'style' => 'margin: 0 5px' 
				) 
		));
		
		$this->setInputFilter(new InputFilter());
	}

	public function getInputFilterSpecification()
	{
		return array (
				'bulk_action' => array (
						'required' => true,
						'filters' => array (
								array (
										'name' => 'Zend\Filter\StringTrim' 
								) 
						) 
				) 
		);
	}

	public function __sleep()
	{
		return array ();
	}
}