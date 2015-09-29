<?php
namespace Account\Form;
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
						'name' => 'submit',
						'type' => 'Zend\Form\Element\Submit',
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Filter',
								'id' => 'filtersubmit',
								'class' => 'btn btn-secondary'
						)
				));
		
		$this->add(
				array(
						'name' => 'description',
						'type' => 'text',
						'required' => false,
						'allow_empty' => true,
						'filters' => array(
								array(
										'name' => 'Zend\Filter\StringTrim'
								)
						),
						'options' => array(
								'label' => 'Search Account(s)',
								'label_attributes' => array(
										'class' => 'sr-only'
								),
								'placeholder' => 'Search Account(s)'
						),
						'attributes' => array(
								'id' => 'accountfilter',
								'placeholder' => 'Search Account(s)'
						)
				));
	}

	public function getInputFilter ()
	{
		if (! $this->inputFilter) {
			$inputFilter = new InputFilter();
			
			$inputFilter->add(
					array(
							'name' => 'description',
							'required' => false,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							),
							'name' => 'referrer',
							'required' => false,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							),
							'name' => 'daterange',
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
