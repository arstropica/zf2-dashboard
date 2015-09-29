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
use Doctrine\Common\Persistence\ObjectManager;

class AttributeValueFieldset extends AbstractFieldset implements 
		InputFilterProviderInterface
{

	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;

	public function __construct (ObjectManager $objectManager, $label = "")
	{
		parent::__construct('lead_attribute_value_fieldset');
		
		$this->setObjectManager($objectManager);
		
		$this->add(
				array(
						'name' => 'importField',
						'type' => 'DoctrineModule\Form\Element\ObjectSelect',
						'options' => array(
								'column-size' => 'xs-12',
								'label' => $label,
								'empty_option' => 'Choose ' . $label . ' Field',
								'object_manager' => $this->getObjectManager(),
								'target_class' => 'Lead\Entity\LeadAttribute',
								'property' => 'attributeDesc',
								'find_method' => array(
										'name' => 'getImportOptions'
								)
						)
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