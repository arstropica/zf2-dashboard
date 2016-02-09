<?php

namespace Report\Form;

use Application\Form\AbstractForm;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Doctrine\ORM\EntityManager;
use Lead\Entity\Lead;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\InputFilter\InputFilter;
use Application\Hydrator\Strategy\DateTimeStrategy;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Report\Form\InputFilter\AddFormInputFilter;

/**
 *
 * @author arstropica
 *        
 */
class EditForm extends AbstractForm implements ObjectManagerAwareInterface, ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var EntityManager
	 */
	protected $entityManager;
	
	/**
	 *
	 * @var InputFilter
	 */
	protected $inputFilter;

	public function __construct()
	{
		parent::__construct('reportEditForm');
	}

	public function init()
	{
		$this->serviceLocator = $this->getFormFactory()
			->getFormElementManager()
			->getServiceLocator();
		
		$hydrator = new DoctrineHydrator($this->getObjectManager(), 'Report\Entity\Report');
		$hydrator->addStrategy('updated', new DateTimeStrategy());
		
		$this->setAttribute('method', 'post')
			->setHydrator($hydrator)
			->setInputFilter(new AddFormInputFilter($this->getServiceLocator()));
		
		$dateTime = date('Y-m-d H:i:s');
		
		$this->add(array (
				'required' => true,
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'id' 
		));
		
		$this->add(array (
				'required' => true,
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'updated' 
		));
		
		$this->add(array (
				'options' => array (
						'label' => 'Name' 
				),
				'required' => true,
				'type' => 'text',
				'name' => 'name' 
		));
		
		$this->add(array (
				'name' => 'account',
				'type' => 'DoctrineModule\Form\Element\ObjectSelect',
				'required' => false,
				'allow_empty' => true,
				'continue_if_empty' => true,
				'options' => array (
						'label' => 'Client Account',
						'empty_option' => 'None',
						'object_manager' => $this->getObjectManager(),
						'target_class' => 'Account\Entity\Account',
						'property' => 'name',
						'find_method' => array (
								'name' => 'findAll' 
						) 
				),
				'attributes' => array (
						'id' => 'account' 
				) 
		));
		
		$this->add(array (
				'name' => 'agent',
				'type' => 'Agent\Form\Fieldset\AgentFieldset',
				'options' => array (
						'label' => "Setup Search Agent",
						'use_as_base_fieldset' => false 
				),
				'attributes' => array (
						'id' => 'agent',
						'class' => 'collection-fieldset' 
				) 
		));
		
		$this->add(array (
				'name' => 'submit',
				'type' => 'Zend\Form\Element\Submit',
				'attributes' => array (
						'type' => 'submit',
						'value' => 'Edit Report',
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

?>