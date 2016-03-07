<?php

namespace Lead\Form\Source;

use Application\Form\AbstractForm;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilter;

/**
 *
 * @author arstropica
 *        
 */
class EditForm extends AbstractForm implements InputFilterAwareInterface {
	
	/**
	 *
	 * @var InputFilter
	 */
	protected $inputFilter;
	
	/**
	 *
	 * @var Array
	 */
	protected $sources;

	public function __construct($sources = [])
	{
		parent::__construct('sourceEditForm');
		
		$this->setSources($sources);
		
		$this->init();
	}

	public function init()
	{
		$this->setAttribute('method', 'post');
		
		$this->add(array (
				'required' => true,
				'type' => 'text',
				'name' => 'source',
				'options' => array (
						'label' => 'Domain (ex. example.com)' 
				) 
		));
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Csrf',
				'name' => 'csrf' 
		));
		
		$this->add(array (
				'name' => 'submit',
				'type' => 'Zend\Form\Element\Submit',
				'attributes' => array (
						'type' => 'submit',
						'value' => 'Edit Source',
						'id' => 'submit',
						'class' => 'btn btn-primary' 
				) 
		));
		
		$this->add(array (
				'name' => 'cancel',
				'type' => 'button',
				'class' => 'btn btn-default',
				'options' => array (
						'label' => 'Cancel',
						'label_attributes' => array (
								'sr-only' 
						) 
				),
				'attributes' => array (
						'value' => 'Cancel',
						'class' => 'btn btn-default' 
				) 
		));
	}

	/**
	 *
	 * @return Array $sources
	 */
	public function getSources()
	{
		return $this->sources;
	}

	/**
	 *
	 * @param Array $sources        	
	 *
	 * @return EditForm
	 */
	public function setSources($sources)
	{
		$this->sources = $sources;
		return $this;
	}

	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			
			$inputFilter->add(array (
					'name' => 'source',
					'required' => true 
			));
			
			$inputFilter->add(array (
					'name' => 'csrf',
					'required' => true 
			));
			
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}

}

?>