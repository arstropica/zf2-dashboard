<?php
namespace Lead\Form\Fieldset;

/**
 *
 * @author arstropica
 *        
 */
use Lead\Entity\Lead;
use Application\Form\Fieldset\AbstractFieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Lead\Entity\LeadAttributeValue;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;

class LeadAttributeValueFieldset extends AbstractFieldset implements InputFilterProviderInterface
{

	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;
	
	
	public function __construct ()
	{
		parent::__construct('lead_attribute_value_fieldset');
	}

	public function init ()
	{
		$this->setHydrator(new DoctrineHydrator($this->getObjectManager(), false))
			->setObject(new LeadAttributeValue());
		
		$this->add(
				array(
						'name' => 'attribute',
						'type' => 'DoctrineModule\Form\Element\ObjectSelect',
						'required' => true,
						'options' => array(
								'column-size' => 'md-4 col-sm-12',
								'label' => 'Attribute',
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
						'options' => array(
								'column-size' => 'md-8 col-sm-12',
								'label' => 'Value'
						),
						'required' => true,
						'type' => 'text',
						'name' => 'value'
				));
	}

	/**
	 * Should return an array specification compatible with
	 * {@link Zend\InputFilter\Factory::createInputFilter()}.
	 *
	 * @return array \
	 */
	public function getInputFilterSpecification ()
	{
		return [];
	}
}

?>