<?php

namespace Lead\Form;

use Application\Form\AbstractForm;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Doctrine\ORM\EntityManager;
use Lead\Entity\Lead;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilter;
use Application\Hydrator\Strategy\DateTimeStrategy;

/**
 *
 * @author arstropica
 *        
 */
class EditForm extends AbstractForm implements ObjectManagerAwareInterface, InputFilterAwareInterface {
	
	/**
	 *
	 * @var EntityManager
	 */
	protected $entityManager;
	
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;
	
	/**
	 *
	 * @var InputFilter
	 */
	protected $inputFilter;

	public function __construct()
	{
		parent::__construct('leadEditForm');
	}

	public function init()
	{
		$this->serviceLocator = $this->getFormFactory()
			->getFormElementManager()
			->getServiceLocator();
		
		$hydrator = new DoctrineHydrator($this->getObjectManager(), 'Lead\Entity\Lead');
		$hydrator->addStrategy('timecreated', new DateTimeStrategy());
		
		$this->setAttribute('method', 'post')
			->setHydrator($hydrator);
		$this->add(array (
				'options' => array (
						'label' => 'Time Created' 
				),
				'required' => true,
				'type' => 'Zend\Form\Element\DateTimeLocal',
				'name' => 'timecreated',
				'attributes' => array (
						'min' => '2010-01-01T00:00:00',
						'readonly' => 'readonly' 
				) 
		));
		
		$this->get('timecreated')
			->setFormat('Y-m-d\TH:i:s');
		
		$this->add(array (
				'options' => array (
						'label' => 'IP Address' 
				),
				'required' => true,
				'type' => 'text',
				'name' => 'ipaddress' 
		));
		
		$this->add(array (
				'options' => array (
						'label' => 'Referrer' 
				),
				'required' => false,
				'type' => 'Zend\Form\Element\Url',
				'name' => 'referrer' 
		));
		
		$this->add(array (
				'name' => 'account',
				'type' => 'DoctrineModule\Form\Element\ObjectSelect',
				'required' => false,
				'allow_empty' => true,
				'continue_if_empty' => true,
				'options' => array (
						'label' => 'Account',
						'empty_option' => 'None',
						'object_manager' => $this->getObjectManager(),
						'target_class' => 'Account\Entity\Account',
						'property' => 'name',
						'find_method' => array (
								'name' => 'findBy',
								'params' => array (
										'criteria' => array (
												'active' => 1 
										) 
								) 
						) 
				),
				'attributes' => array (
						'id' => 'account' 
				) 
		));
		
		$this->add(array (
				'type' => 'Application\Form\Element\Collection',
				'name' => 'attributes',
				'required' => true,
				'options' => array (
						'label' => 'Add Lead Details',
						// 'count' => 8,
						'should_create_template' => true,
						'template_placeholder' => '__index__',
						'allow_add' => true,
						'allow_remove' => true,
						'target_element' => array (
								'type' => 'Lead\Form\Fieldset\LeadAttributeValueFieldset' 
						) 
				),
				'attributes' => array (
						'id' => 'leadAttributes' 
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
						'value' => 'Save Changes',
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

	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			
			$inputFilter->add(array (
					'name' => 'timecreated',
					'required' => true 
			));
			
			$inputFilter->add(array (
					'name' => 'ipaddress',
					'required' => true,
					'filters' => array (
							array (
									'name' => 'Zend\Filter\StringTrim' 
							) 
					) 
			));
			
			$inputFilter->add(array (
					'name' => 'referrer',
					'required' => false,
					'filters' => array (
							array (
									'name' => 'Zend\Filter\StringTrim' 
							) 
					) 
			));
			
			$inputFilter->add(array (
					'name' => 'account',
					'required' => false 
			));
			
			$inputFilter->add(array (
					'name' => 'attributes',
					'required' => true 
			));
			
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}

	/**
	 *
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		if (!$this->entityManager) {
			$entityManager = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
			$this->setEntityManager($entityManager);
		}
		
		return $this->entityManager;
	}

	public function setEntityManager(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
		return $this;
	}
}