<?php
namespace Lead\Form\Attribute;
use Application\Form\AbstractForm;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @author arstropica
 *        
 */
class MergeForm extends AbstractForm implements ObjectManagerAwareInterface, 
		InputFilterAwareInterface
{
	
	use ServiceLocatorAwareTrait;

	/**
	 *
	 * @var InputFilter
	 */
	protected $inputFilter;

	public function __construct ()
	{
		parent::__construct('attributeMergeForm');
	}

	public function init ()
	{
		$serviceLocator = $this->getFormFactory()
			->getFormElementManager()
			->getServiceLocator();
		
		$this->setServiceLocator($serviceLocator);
		
		$this->setAttribute('method', 'post');
		
		$this->add(
				array(
						'required' => true,
						'type' => 'hidden',
						'name' => 'attribute'
				));
		
		$this->add(
				array(
						'name' => 'merge',
						'type' => 'DoctrineModule\Form\Element\ObjectSelect',
						'attributes' => array(
								'id' => 'merge'
						),
						'options' => array(
								'column-size' => 'xs-12',
								'label' => 'Merge with:',
								'empty_option' => 'Choose Attribute',
								'object_manager' => $this->getObjectManager(),
								'target_class' => 'Lead\Entity\LeadAttribute',
								'property' => 'attributeDesc',
								'find_method' => array(
										'name' => 'findUnique'
								)
						)
				));
		
		$this->add(
				array(
						'type' => 'Zend\Form\Element\Csrf',
						'name' => 'csrf'
				));
		
		$this->add(
				array(
						'name' => 'submit',
						'type' => 'Zend\Form\Element\Submit',
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Merge Attribute',
								'id' => 'submit',
								'class' => 'btn btn-primary'
						)
				));
		
		$this->add(
				array(
						'name' => 'cancel',
						'type' => 'button',
						'class' => 'btn btn-default',
						'options' => array(
								'label' => 'Cancel',
								'label_attributes' => array(
										'sr-only'
								)
						),
						'attributes' => array(
								'value' => 'Cancel',
								'class' => 'btn btn-default'
						)
				));
	}

	public function getInputFilter ()
	{
		if (! $this->inputFilter) {
			$inputFilter = new InputFilter();
			
			$inputFilter->add(
					array(
							'name' => 'attribute',
							'required' => true
					));
			
			$inputFilter->add(
					array(
							'name' => 'merge',
							'required' => true,
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

?>