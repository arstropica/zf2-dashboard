<?php

namespace Agent\Form\Fieldset;

use Zend\InputFilter\InputFilterProviderInterface;
use Application\Form\Fieldset\AbstractFieldset;
use Agent\Entity\AgentCriterion;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;

/**
 *
 * @author arstropica
 *        
 */
class AgentCriterionFieldset extends AbstractFieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;

	public function __construct()
	{
		parent::__construct('criteria');
	}

	public function init()
	{
		$hydrator = new DoctrineHydrator($this->getObjectManager(), 'Agent\Entity\AgentCriterion');
		$this->setHydrator($hydrator)
			->setObject(new AgentCriterion());
		
		$this->setAttribute('class', $this->getAttribute('class') . ' addremove-fieldset');
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'id',
				'required' => false 
		));
		
		$this->add(array (
				'name' => 'attribute',
				'type' => 'DoctrineModule\Form\Element\ObjectSelect',
				'required' => true,
				'options' => array (
						'column-size' => 'md-5 col-sm-12',
						'label' => 'Attribute',
						'empty_option' => 'Choose Attribute',
						'object_manager' => $this->getObjectManager(),
						'target_class' => 'Lead\Entity\LeadAttribute',
						'property' => 'attributeDesc',
						'find_method' => array (
								'name' => 'findUnique' 
						) 
				),
				'attributes' => array (
						'class' => 'attribute' 
				) 
		));
		
		$this->add(array (
				'name' => 'relationship',
				'type' => 'DoctrineModule\Form\Element\ObjectSelect',
				'required' => true,
				'options' => array (
						'column-size' => 'md-3 col-sm-6',
						'label' => 'Comparison',
						'empty_option' => 'Choose Comparison',
						'object_manager' => $this->getObjectManager(),
						'target_class' => 'Agent\Entity\Relationship',
						'property' => 'label',
						'find_method' => array (
								'name' => 'getLabels' 
						) 
				),
				'attributes' => array (
						'style' => 'margin: 0 5px',
						'class' => 'relationship',
						'disabled' => 'disabled' 
				) 
		));
		
		$this->add(array (
				'options' => array (
						'column-size' => 'md-2 col-sm-6',
						'label' => 'Weight' 
				),
				'attributes' => array (
						'value' => '0.5',
						'class' => 'weight has-chosen',
						'min' => '0',
						'max' => '1',
						'step' => '0.1' 
				),
				'required' => false,
				'type' => 'Zend\Form\Element\Number',
				'name' => 'weight' 
		));
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Select',
				'name' => 'required',
				'required' => true,
				'allow_empty' => false,
				'continue_if_empty' => false,
				'options' => array (
						'column-size' => 'md-2 col-sm-6',
						'label' => 'Required',
						'value_options' => array (
								0 => 'False',
								1 => 'True' 
						) 
				),
				'attributes' => array (
						'class' => 'has-chosen _required' 
				) 
		));
		
		$this->add(array (
				'name' => 'value',
				'type' => 'Agent\Form\Fieldset\AgentCriterionValueFieldset',
				'required' => false,
				'options' => array (
						'column-size' => 'md-4 col-sm-8 col-xs-12',
						'use_as_base_fieldset' => false 
				),
				'attributes' => array (
						'id' => 'values',
						'class' => 'collection-fieldset values-fieldset col-xs-12 col-centered clearfix' 
				) 
		));
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\InputFilter\InputFilterProviderInterface::getInputFilterSpecification()
	 *
	 */
	public function getInputFilterSpecification()
	{
		return array (
				'id' => array (
						'required' => false 
				),
				'attribute' => array (
						'required' => true,
						'validators' => array (
								array (
										'name' => 'NotEmpty' 
								) 
						) 
				),
				'relationship' => array (
						'required' => true 
				),
				'weight' => array (
						'required' => false 
				),
				'required' => array (
						'required' => true 
				) 
		);
	}

}

?>