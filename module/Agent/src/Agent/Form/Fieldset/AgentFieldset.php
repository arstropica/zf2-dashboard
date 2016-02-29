<?php

namespace Agent\Form\Fieldset;

use Zend\InputFilter\InputFilterProviderInterface;
use Application\Form\Fieldset\AbstractFieldset;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Hydrator\Strategy\DateTimeStrategy;
use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Agent\Entity\Agent;

/**
 *
 * @author arstropica
 *        
 */
class AgentFieldset extends AbstractFieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;

	public function __construct()
	{
		parent::__construct('agent');
	}

	public function init()
	{
		$hydrator = new DoctrineHydrator($this->getObjectManager(), 'Agent\Entity\Agent');
		$hydrator->addStrategy('updated', new DateTimeStrategy());
		
		$this->setAttribute('method', 'post')
			->setHydrator($hydrator)
			->setObject(new Agent());
		
		$dateTime = date('Y-m-d H:i:s');
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'id',
				'required' => false 
		));
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'updated',
				'required' => true,
				'attributes' => array (
						'value' => $dateTime 
				) 
		));
		
		$this->add(array (
				'name' => 'filter',
				'type' => 'Agent\Form\Fieldset\FilterFieldset',
				'options' => array (
						'label' => "Filters",
						'use_as_base_fieldset' => false 
				),
				'attributes' => array (
						'id' => 'filter',
						'class' => 'collection-fieldset' 
				) 
		));
		
		$this->add(array (
				'type' => 'Application\Form\Element\Collection',
				'name' => 'criteria',
				'required' => false,
				'options' => array (
						'column-size' => 'md-12',
						'label' => '3. Add Criteria',
						'count' => 1,
						'should_create_template' => true,
						'template_placeholder' => '__index__',
						'allow_add' => true,
						'allow_remove' => true,
						'target_element' => array (
								'type' => 'Agent\Form\Fieldset\AgentCriterionFieldset' 
						) 
				),
				'attributes' => array (
						'id' => 'criteria',
						'class' => 'collection-fieldset criteria-fieldset' 
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
				'updated' => array (
						'required' => true,
						'filters' => array (
								array (
										'name' => 'StringTrim' 
								) 
						) 
				) 
		);
	}
}

?>