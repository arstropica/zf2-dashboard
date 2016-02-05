<?php

namespace Lead\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;
use Lead\Form\Fieldset\ImportFieldset;
use Zend\InputFilter\InputFilterAwareInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;

class ImportForm extends Form implements InputFilterAwareInterface, ObjectManagerAwareInterface {
	
	/**
	 *
	 * @var EntityManager
	 */
	protected $entityManager;
	
	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;
	
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	public function __construct(EntityManager $entityManager)
	{
		parent::__construct('leadimport');
		
		$this->entityManager = $entityManager;
	}

	public function init()
	{
		$this->setAttribute('method', 'post')
			->setAttribute('enctype', 'multipart/form-data')
			->setHydrator(new ClassMethodsHydrator(false));
		
		$this->serviceLocator = $this->getFormFactory()
			->getFormElementManager()
			->getServiceLocator();
		
		$entityManager = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
		$this->setEntityManager($entityManager);
		
		$objectManager = $this->serviceLocator->get('Doctrine\ORM\EntityManager');
		$this->setObjectManager($objectManager);
		
		$this->add(array (
				'name' => 'leadTmpFile',
				'attributes' => array (
						'type' => 'hidden' 
				) 
		));
		
		$this->add(array (
				'name' => 'submit',
				'type' => 'Zend\Form\Element\Submit',
				'attributes' => array (
						'type' => 'submit',
						'value' => 'Upload',
						'id' => 'submit',
						'class' => 'btn btn-primary' 
				) 
		));
	}

	public function addConfirmField()
	{
		$this->add(array (
				'name' => 'confirm',
				'attributes' => array (
						'value' => 1,
						'type' => 'hidden' 
				),
				'options' => array (
						'label' => 'Confirm Import' 
				) 
		));
	}

	public function addReviewField()
	{
		$this->add(array (
				'name' => 'review',
				'attributes' => array (
						'value' => 1,
						'type' => 'hidden' 
				),
				'options' => array (
						'label' => 'Potential Duplicate Leads' 
				) 
		));
	}

	public function addCancelField()
	{
		$url = $this->serviceLocator->get('viewhelpermanager')
			->get('url');
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
						'class' => 'btn btn-default',
						'onClick' => 'top.location=\'' . $url('import', [ ], true) . '\'' 
				) 
		));
	}

	public function addHiddenField($name, $value, $options = [])
	{
		$this->add(array (
				'name' => $name,
				'attributes' => array (
						'value' => $value,
						'type' => 'hidden' 
				),
				'options' => $options 
		));
	}

	public function addUploadField()
	{
		$this->add(array (
				'name' => 'leadsUpload',
				'attributes' => array (
						'type' => 'file',
						'id' => 'leads-upload' 
				),
				'options' => array (
						'label' => 'Upload Leads' 
				) 
		));
		$this->addInputFilter();
	}

	public function addImportFieldset($options = array(), $isAdmin = false)
	{
		$objectManager = $this->getObjectManager();
		$this->add(array (
				'options' => array (
						'column-size' => ' typeahead-container',
						'label' => 'Account / Company Name (Optional)' 
				),
				'attributes' => array (
						'class' => 'input-lg' 
				),
				'required' => false,
				'type' => 'text',
				'name' => 'Company' 
		));
		
		$leadImportFieldset = new ImportFieldset($objectManager, $options, $isAdmin);
		$leadImportFieldset->setName('match')
			->setOptions(array (
				'label' => "Enter or Select matching fields for your data.",
				'use_as_base_fieldset' => false 
		));
		$this->add($leadImportFieldset);
		return $leadImportFieldset;
	}

	public function addLeadFieldset($name = 'leads', $leadData = array())
	{
		$leadAttributeValuesFieldset = new Fieldset($name, [ 
				'use_as_base_fieldset' => false 
		]);
		
		if ($leadData) {
			foreach ( $leadData as $i => $lead ) {
				foreach ( $lead as $field => $value ) {
					$leadAttributeValuesFieldset->add(array (
							'name' => "{$i}[{$field}]",
							'attributes' => array (
									'value' => $value,
									'type' => 'hidden' 
							) 
					));
				}
			}
		}
		
		$this->add($leadAttributeValuesFieldset);
		return $leadAttributeValuesFieldset;
	}

	public function getImportFieldset($options = [], $isAdmin = false)
	{
		$objectManager = $this->getObjectManager();
		return new ImportFieldset($objectManager, $options, $isAdmin);
	}

	public function addInputFilter()
	{
		$inputFilter = new InputFilter();
		$factory = new InputFactory();
		
		$inputFilter->add($factory->createInput(array (
				'name' => 'leadsUpload',
				'required' => true 
		)));
		
		$this->setInputFilter($inputFilter);
	}

	public function getLeadAttributes()
	{
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository("Lead\\Entity\\LeadAttribute");
		
		return $objRepository->getUniqueArray();
	}

	public function getAttributeFields($criteria = [])
	{
		$em = $this->getEntityManager();
		$attributeRepository = $em->getRepository("Lead\\Entity\\LeadAttribute");
		
		$attributes = $attributeRepository->getUniqueArray(true, $criteria);
		
		return array_merge($attributes, [ 
				'Time Created' => 'timecreated',
				'Referrer' => 'referrer',
				'IP Address' => 'ipaddress' 
		]);
	}

	/**
	 *
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}

	public function setEntityManager(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
		return $this;
	}

	/**
	 * Set the object manager
	 *
	 * @param ObjectManager $objectManager        	
	 */
	public function setObjectManager(ObjectManager $objectManager)
	{
		$this->objectManager = $objectManager;
	}

	/**
	 * Get the object manager
	 *
	 * @return ObjectManager
	 */
	public function getObjectManager()
	{
		return $this->objectManager;
	}

	public function __sleep()
	{
		$data = false;
		try {
			if ($this->isValid()) {
				$data = $this->getData();
			} else {
				$data = false;
			}
		} catch ( \Exception $e ) {
			$data = false;
		}
		if ($data) {
			$this->storage = array_filter($data);
			return [ 
					'storage' 
			];
		}
		return [ ];
	}

	public function __wakeup()
	{
		// ...
	}
}