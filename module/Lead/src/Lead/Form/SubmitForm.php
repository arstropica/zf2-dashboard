<?php
namespace Lead\Form;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;

class SubmitForm extends Form implements InputFilterAwareInterface
{

	public function __construct ($name, $options = [])
	{
		parent::__construct($name, $options);
		
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
								'value' => 'Batch Submit',
								'id' => 'listsubmit',
								'class' => 'btn btn-primary'
						)
				));
		
		$this->setInputFilter(new InputFilter());
	}
}