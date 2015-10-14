<?php
namespace Lead\Form\Attribute;
use Application\Form\AbstractForm;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilter;

/**
 *
 * @author arstropica
 *        
 */
class AddForm extends AbstractForm implements ObjectManagerAwareInterface, 
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
		parent::__construct('attributeAddForm');
	}

	public function init ()
	{
		$this->serviceLocator = $this->getFormFactory()
			->getFormElementManager()
			->getServiceLocator();
		
		$hydrator = new DoctrineHydrator($this->getObjectManager(), 
				'Lead\Entity\LeadAttribute');
		
		$this->setAttribute('method', 'post')->setHydrator($hydrator);
		
		$this->add(
				array(
						'options' => array(
								'label' => 'Name',
								'label_attributes' => array(
										'class' => 'sr-only'
								)
						),
						'attributes' => array(
								'value' => 'Question'
						),
						'required' => true,
						'type' => 'hidden',
						'name' => 'attributeName'
				));
		
		$this->add(
				array(
						'options' => array(
								'label' => 'Description'
						),
						'required' => true,
						'type' => 'text',
						'name' => 'attributeDesc'
				));
		
		$this->add(
				array(
						'type' => 'Zend\Form\Element\Csrf',
						'name' => 'csrf'
				));
		
		$this->add(
				array(
						'name' => 'submit',
						'type' => 'Zend\Form\Element\Submit',
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Add Attribute',
								'id' => 'submit',
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

	public function getInputFilter ()
	{
		if (! $this->inputFilter) {
			$inputFilter = new InputFilter();
			
			$inputFilter->add(
					array(
							'name' => 'attributeName',
							'required' => true,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							)
					));
			
			$inputFilter->add(
					array(
							'name' => 'attributeDesc',
							'required' => true,
							'filters' => array(
									array(
											'name' => 'Zend\Filter\StringTrim'
									)
							)
					));
			
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}

	/**
	 *
	 * @return EntityManager
	 */
	public function getEntityManager ()
	{
		if (! $this->entityManager) {
			$entityManager = $this->serviceLocator->get(
					'doctrine.entitymanager.orm_default');
			$this->setEntityManager($entityManager);
		}
		
		return $this->entityManager;
	}

	public function setEntityManager (EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
		return $this;
	}
}

?>