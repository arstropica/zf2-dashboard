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
				'name' => 'account',
				'type' => 'DoctrineModule\Form\Element\ObjectSelect',
				'required' => false,
				'allow_empty' => true,
				'continue_if_empty' => true,
				'options' => array (
						'column-size' => 'xs-12 col-sm-8 col-md-10',
						'label' => 'Filter Account',
						'label_attributes' => array (
								'class' => 'horizontal' 
						),
						'empty_option' => 'None',
						'object_manager' => $this->getObjectManager(),
						'target_class' => 'Account\Entity\Account',
						'property' => 'name',
						'find_method' => array (
								'name' => 'findAll' 
						) 
				),
				'attributes' => array (
						'id' => 'accountFilter' 
				) 
		));
		
		$this->add(array (
				'type' => 'select',
				'name' => 'orphan',
				'required' => false,
				'allow_empty' => true,
				'continue_if_empty' => true,
				'options' => array (
						'column-size' => 'xs-6 col-sm-4 col-md-2',
						'label' => 'Unassigned Only',
						'label_attributes' => array (
								'class' => 'horizontal' 
						),
						'value_options' => array (
								'0' => 'No',
								'1' => 'Yes' 
						) 
				),
				'attributes' => array (
						'id' => 'orphan',
						'class' => 'has-chosen',
						'value' => '1' 
				) 
		));
		
		$this->add(array (
				'type' => 'Application\Form\Element\Collection',
				'name' => 'criteria',
				'required' => false,
				'options' => array (
						'column-size' => 'md-12',
						'label' => '2. Add Criteria',
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
				),
				'account' => array (
						'required' => false 
				),
				'orphan' => array (
						'required' => false 
				) 
		);
	}
}

?>