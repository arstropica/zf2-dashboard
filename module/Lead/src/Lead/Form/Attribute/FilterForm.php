<?php
namespace Lead\Form\Attribute;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterAwareInterface;
use Doctrine\ORM\EntityManager;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\ServiceLocatorInterface;

class FilterForm extends Form implements InputFilterAwareInterface
{

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

	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	protected $url;

	public function __construct (EntityManager $entityManager)
	{
		parent::__construct();
		
		$this->entityManager = $entityManager;
	}

	public function init ()
	{
		$this->serviceLocator = $this->getFormFactory()
			->getFormElementManager()
			->getServiceLocator();
		
		$this->url = $this->serviceLocator->get('viewhelpermanager')->get('url');
		
		$r = $this->serviceLocator->get('Application')
			->getMvcEvent()
			->getRouteMatch();
		
		$rq = $this->serviceLocator->get('Request');
		
		$this->setAttribute('action', 
				$this->url->__invoke($r->getMatchedRouteName(), [], 
						[
								'query' => $rq->getQuery()
									->toArray()
						], true));
		
		$this->setAttribute('method', 'GET');
		
		$this->add(
				array(
						'name' => 'submit',
						'type' => 'Zend\Form\Element\Submit',
						'attributes' => array(
								'type' => 'submit',
								'value' => 'Filter',
								'id' => 'filtersubmit',
								'class' => 'btn btn-secondary'
						)
				));
		
		$this->add(
				array(
						'name' => 'attributeDesc',
						'type' => 'text',
						'required' => false,
						'allow_empty' => true,
						'filters' => array(
								array(
										'name' => 'Zend\Filter\StringTrim'
								)
						),
						'options' => array(
								'label' => 'Description',
								'label_attributes' => array(
										'class' => 'sr-only'
								)
						),
						'attributes' => array(
								'id' => 'attributeDesc',
								'style' => 'max-width: 80%; min-width: 250px; width: 400px;'
						)
				));
	}

	public function getInputFilter ()
	{
		if (! $this->inputFilter) {
			$inputFilter = new InputFilter();
			
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
