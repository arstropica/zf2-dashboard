<?php
namespace Api\Form;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Doctrine\ORM\EntityManager;

class ListForm extends Form implements InputFilterAwareInterface
{

	/**
	 *
	 * @var EntityManager
	 */
	protected $entityManager;

	public function __construct (EntityManager $entityManager)
	{
		parent::__construct();
		
		$this->entityManager = $entityManager;
	}

	public function init ()
	{
		$this->add(
				array(
						'type' => 'Zend\Form\Element\Csrf',
						'name' => 'csrf',
						'attributes' => array(
								'id' => 'csrf'
						)
				));
		
		$this->add(
				array(
						'name' => 'account',
						'type' => 'DoctrineModule\Form\Element\ObjectSelect',
						'required' => true,
						'allow_empty' => false,
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
								'empty_option' => 'Choose Account',
								'object_manager' => $this->entityManager,
								'target_class' => 'Account\Entity\Account',
								'property' => 'name',
								'is_method' => true,
								'find_method' => array(
										'name' => 'getAccounts'
								)
						),
						'attributes' => array(
								'id' => 'accountfilter'
						)
				));
		
		$this->add(
				array(
						'name' => 'submit',
						'type' => 'Zend\Form\Element\Submit',
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Batch Submit',
								'id' => 'listsubmit',
								'class' => 'btn btn-primary'
						)
				));
		
		$this->setInputFilter(new InputFilter());
	}
}