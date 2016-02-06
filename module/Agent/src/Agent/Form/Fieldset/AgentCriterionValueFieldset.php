<?php

namespace Agent\Form\Fieldset;

use Zend\InputFilter\InputFilterProviderInterface;
use Application\Form\Fieldset\AbstractFieldset;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Agent\Entity\AgentCriterionValue;
use Agent\Entity\Strategy\AgentCriterionValueStrategy;

/**
 *
 * @author arstropica
 *        
 */
class AgentCriterionValueFieldset extends AbstractFieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;

	public function __construct()
	{
		parent::__construct('value');
	}

	public function init()
	{
		$hydrator = new DoctrineHydrator($this->getObjectManager(), 'Agent\Entity\AgentCriterionValue');
		$hydrator->addStrategy('value', new AgentCriterionValueStrategy());
		$this->setHydrator($hydrator)
			->setObject(new AgentCriterionValue());
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'id',
				'required' => false 
		));
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'type',
				'required' => true,
				'attributes' => array (
						'class' => 'type' 
				) 
		));
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'value',
				'required' => false,
				'attributes' => array (
						'class' => 'value' 
				) 
		));
		
		$this->add(array (
				'options' => array (
						'label' => 'Enter Value:',
						'label_attributes' => array (
								'class' => '' 
						) 
				),
				'attributes' => array (
						'class' => 'criterion string',
						'placeholder' => 'Enter Value' 
				),
				'type' => 'text',
				'name' => 'string' 
		));
		
		$this->add(array (
				'options' => array (
						'label' => 'Choose Value(s): ',
						'label_attributes' => array (
								'class' => 'sr-only' 
						),
						'empty_option' => 'Select Value(s)',
						'value_options' => array () 
				),
				'attributes' => array (
						'class' => 'criterion multiple',
						'multiple' => 'multiple' 
				),
				'type' => 'select',
				'name' => 'multiple' 
		));
		
		$this->add(array (
				'options' => array (
						'label' => 'Select a Boolean Value: ',
						'label_attributes' => array (
								'class' => 'horizontal' 
						),
						'value_options' => array (
								0 => 'No',
								1 => 'Yes' 
						) 
				),
				'attributes' => array (
						'class' => 'criterion boolean horizontal',
						'style' => 'width: 100px' 
				),
				'type' => 'select',
				'name' => 'boolean' 
		));
		
		$this->add(array (
				'name' => 'daterange',
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'type' => 'Application\Form\Element\DateRange',
				'options' => array (
						'column-size' => 'xs-12 col-sm-6 col-md-4',
						'label' => 'Select Date Range: ',
						'label_attributes' => array (
								'class' => '' 
						) 
				),
				'attributes' => array (
						'class' => 'criterion daterange',
						'id' => 'daterange_' . uniqid() 
				) 
		));
		
		$this->add(array (
				'name' => 'location',
				'type' => 'Agent\Form\Fieldset\LocationFieldset',
				'use_as_base_fieldset' => false,
				'attributes' => array (
						'class' => 'criterion location group-fieldset',
						'id' => 'location_' . uniqid() 
				) 
		));
		
		$this->add(array (
				'type' => 'Application\Form\Element\Slider',
				'name' => 'range',
				'options' => array (
						'label' => 'Select Range: ',
						'label_attributes' => array (
								'class' => 'horizontal' 
						) 
				),
				'attributes' => array (
						'data-slider-min' => 0, // default minimum is 0
						'data-slider-max' => 100, // default maximum is 100
						'data-slider-step' => 1, // default interval is 1
						'data-slider-range' => true,
						'data-slider-tick' => '[0, 25, 50, 75, 100]',
						'data-slider-ticks_labels' => '["0", "25", "50", "75", "100"]',
						'data-slider-handle' => 'square',
						'class' => 'criterion range slider horizontal',
						'id' => 'slider_' . uniqid() 
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
		$spec = array (
				'id' => array (
						'required' => false 
				),
				'type' => array (
						'required' => true,
						'filters' => array (
								array (
										'name' => 'Zend\Filter\StringTrim' 
								) 
						) 
				),
				'value' => array (
						'required' => false 
				) 
		);
		$fields = [ 
				'string',
				'multiple',
				'boolean',
				'location',
				'daterange',
				'range' 
		];
		
		foreach ( $fields as $field ) {
			$spec [$field] = array (
					'required' => true 
			);
		}
		return $spec;
	}

}

?>