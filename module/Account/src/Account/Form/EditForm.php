<?php
namespace Account\Form;
use Application\Form\AbstractForm;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Zend\InputFilter\InputFilterAwareInterface;
use Doctrine\ORM\EntityManager;
use Zend\InputFilter\InputFilter;
use Account\Entity\Account;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @author arstropica
 *        
 */
class EditForm extends AbstractForm implements ObjectManagerAwareInterface, 
		InputFilterAwareInterface
{

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

	public function __construct ()
	{
		parent::__construct('accountEditForm');
	}

	public function init ()
	{
		$this->serviceLocator = $this->getFormFactory()
			->getFormElementManager()
			->getServiceLocator();
		
		$entityManager = $this->serviceLocator->get(
				'doctrine.entitymanager.orm_default');
		$this->setEntityManager($entityManager);
		
		$this->setAttribute('method', 'post')->setHydrator(
				new DoctrineHydrator($this->getEntityManager(), 
						'Account\Entity\Account'));
		
		$this->add(
				array(
						'name' => 'apis',
						'type' => 'DoctrineModule\Form\Element\ObjectSelect',
						'required' => true,
						'allow_empty' => false,
						'continue_if_empty' => true,
						'options' => array(
								'label' => 'Active API(s)',
								'empty_option' => 'None',
								'object_manager' => $this->getObjectManager(),
								'target_class' => 'Api\Entity\Api',
								'property' => 'name',
								'find_method' => array(
										'name' => 'findAll'
								)
						),
						'attributes' => array(
								'multiple' => 'multiple',
								'id' => 'accountAPIs',
								'class' => 'col-xs-12 col-sm-6 col-md-4 col-lg-3'
						)
				));
		
		$this->add(
				array(
						// 'type' => 'Zend\Form\Element\Collection',
						'type' => 'Application\Form\Element\Collection',
						'name' => 'apiSettings',
						'options' => array(
								'label' => 'Add/Edit API Settings',
								'count' => 1,
								'should_create_template' => true,
								'template_placeholder' => '__index__',
								'allow_add' => true,
								'allow_remove' => true,
								'target_element' => array(
										'type' => 'Account\Form\Api\Fieldset\ApiSettingFieldset'
								)
						),
						'attributes' => array(
								'id' => 'apiSettings'
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
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Save',
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

	/**
	 *
	 * @return EntityManager
	 */
	public function getEntityManager ()
	{
		return $this->entityManager;
	}

	public function setEntityManager (EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
		return $this;
	}

	public function getInputFilter ()
	{
		if (! $this->inputFilter) {
			$inputFilter = new InputFilter();
			
			$inputFilter->add(
					array(
							'name' => 'apis',
							'required' => false,
					));
			
			$inputFilter->add(
					array(
							'name' => 'apiSettings',
							'required' => false,
					));
			
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}
}
