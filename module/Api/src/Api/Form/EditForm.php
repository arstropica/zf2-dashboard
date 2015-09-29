<?php
namespace Api\Form;
use Application\Form\AbstractForm;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Doctrine\ORM\EntityManager;
use Api\Entity\Api;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @author arstropica
 *        
 */
class EditForm extends AbstractForm implements ObjectManagerAwareInterface
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

	public function __construct ()
	{
		parent::__construct('apiEditForm');
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
				new DoctrineHydrator($this->getEntityManager(), 'Api\Entity\Api'));
		
		$this->setAttribute('id', 'globalApiOptions');
		
		$this->add(
				array(
						// 'type' => 'Zend\Form\Element\Collection',
						'type' => 'Application\Form\Element\Collection',
						'name' => 'options',
						'options' => array(
								'label' => 'API Settings',
								'count' => 1,
								'should_create_template' => true,
								'template_placeholder' => '__index__',
								'allow_add' => false,
								'allow_remove' => false,
								'target_element' => array(
										'type' => 'Api\Form\Fieldset\ApiOptionFieldset'
								)
						),
						'attributes' => array(
								'id' => 'options'
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
}
