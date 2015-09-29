<?php
namespace Account\Form;
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
						'name' => 'submit',
						'type' => 'Zend\Form\Element\Submit',
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Delete Account(s)',
								'id' => 'listsubmit',
								'class' => 'btn btn-danger'
						)
				));
		
		$this->setInputFilter(new InputFilter());
	}

	public function addConfirm ()
	{
		$this->add(
				array(
						'name' => 'confirm',
						'type' => 'Zend\Form\Element\Submit',
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Confirm',
								'id' => 'listsubmit',
								'class' => 'btn btn-danger'
						)
				));
		
		return $this;
	}
}