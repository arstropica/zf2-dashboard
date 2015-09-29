<?php
namespace Api\Form;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterAwareInterface;
use Doctrine\ORM\EntityManager;
use Zend\InputFilter\InputFilter;

class FilterForm extends Form implements InputFilterAwareInterface
{

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

	public function __construct (EntityManager $entityManager)
	{
		parent::__construct();
		
		$this->entityManager = $entityManager;
	}

	public function init ()
	{
		$this->setAttribute('method', 'GET');
		
		$this->add(
				array(
						'name' => 'account',
						'type' => 'DoctrineModule\Form\Element\ObjectSelect',
						'required' => false,
						'allow_empty' => true,
						'filters' => array(
								array(
										'name' => 'Zend\Filter\StringTrim'
								)
						),
						'options' => array(
								'label' => 'Accounts',
								'label_attributes' => array(
										'class' => 'sr-only'
								),
								'empty_option' => 'All Accounts',
								'object_manager' => $this->entityManager,
								'target_class' => 'Account\Entity\Account',
								'property' => 'name',
								'is_method' => true,
								'find_method' => array(
										'name' => 'getNames'
								)
						),
						'attributes' => array(
								'id' => 'accountfilter'
						)
				));
		
		$this->add(
				array(
						'name' => 'api',
						'type' => 'DoctrineModule\Form\Element\ObjectSelect',
						'required' => false,
						'allow_empty' => true,
						'filters' => array(
								array(
										'name' => 'Zend\Filter\StringTrim'
								)
						),
						'options' => array(
								'label' => 'API',
								'label_attributes' => array(
										'class' => 'sr-only'
								),
								'empty_option' => 'All APIs',
								'object_manager' => $this->entityManager,
								'target_class' => 'Api\Entity\Api',
								'property' => 'name',
								'is_method' => true,
								'find_method' => array(
										'name' => 'findAll'
								)
						),
						'attributes' => array(
								'id' => 'apifilter'
						)
				));
		
		$this->add(
				array(
						'name' => 'submit',
						'type' => 'Zend\Form\Element\Submit',
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Filter',
								'id' => 'filtersubmit',
								'class' => 'btn btn-secondary'
						)
				));
	}

	public function getInputFilter ()
	{
		if (! $this->inputFilter) {
			$inputFilter = new InputFilter();
			
			$inputFilter->add(
					array(
							'name' => 'account',
							'required' => false,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							),
							'name' => 'api',
							'required' => false,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							),
							'name' => 'apiOption',
							'required' => false,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							)
					));
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
}
