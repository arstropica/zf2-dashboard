<?php

namespace Lead\Form\Fieldset;

use Application\Form\Fieldset\AbstractFieldset;
use Zend\InputFilter\InputProviderInterface;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Application\Provider\EntityManagerAwareTrait;

/**
 *
 * @author arstropica
 *        
 */
class LeadFilterFieldset extends AbstractFieldset implements InputProviderInterface, ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait, EntityManagerAwareTrait;
	
	/**
	 *
	 * @var EntityManager
	 */
	protected $entityManager;

	public function __construct()
	{
		parent::__construct('filters');
	}

	public function init()
	{
		$serviceLocator = $this->getFormFactory()
			->getFormElementManager()
			->getServiceLocator();
		
		$this->setServiceLocator($serviceLocator);
		
		$entityManager = $this->getServiceLocator()
			->get('Doctrine\ORM\EntityManager');
		
		$this->entityManager = $entityManager;
		
		$this->add(array (
				'name' => 'account',
				'type' => 'DoctrineModule\Form\Element\ObjectSelect',
				'required' => false,
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'options' => array (
						'label' => 'Accounts',
						'label_attributes' => array (
								'class' => 'sr-only' 
						),
						'empty_option' => 'All Accounts',
						'object_manager' => $this->entityManager,
						'target_class' => 'Account\Entity\Account',
						'property' => 'name',
						'is_method' => true,
						'find_method' => array (
								'name' => 'getNames' 
						) 
				),
				'attributes' => array (
						'class' => 'leadFilter',
						'id' => 'accountfilter',
						'data-placeholder' => 'Filter Account',
						'onchange' => '$(".accountFilter").val(this.value);' 
				) 
		));
		
		$repository = $this->getEntityManager()
			->getRepository('Lead\Entity\Lead');
		$referrers = $repository->getReferrers(0);
		
		$this->add(array (
				'name' => 'description',
				'required' => false,
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'type' => 'text',
				'options' => array (
						'label' => 'Name',
						'label_attributes' => array (
								'class' => 'sr-only' 
						) 
				),
				'attributes' => array (
						'id' => 'descriptionfilter',
						'placeholder' => 'Filter Name(s)',
						'class' => 'leadFilter has-chosen description typeahead',
						'data-placeholder' => 'Filter Name(s)',
						'onchange' => '$(".descriptionFilter").val(this.value);' 
				) 
		));
		
		$this->add(array (
				'name' => 'referrer',
				'type' => 'Zend\Form\Element\Select',
				'required' => false,
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'options' => array (
						'label' => 'Sources',
						'label_attributes' => array (
								'class' => 'sr-only' 
						),
						'value_options' => $referrers,
						'empty_option' => 'All Sources' 
				),
				'attributes' => array (
						'class' => 'leadFilter',
						'id' => 'referrerfilter',
						'data-placeholder' => 'Filter Referrer',
						'onchange' => '$(".referrerFilter").val(this.value);' 
				) 
		));
		
		$this->add(array (
				'name' => 'lastsubmitted',
				'required' => false,
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'type' => 'Application\Form\Element\DateRange',
				'options' => array (
						'label' => 'Date Range',
						'label_attributes' => array (
								'class' => 'sr-only' 
						) 
				),
				'attributes' => array (
						'class' => 'leadFilter',
						'id' => 'lastsubmittedFilter',
						'onchange' => '$(".lastsubmittedFilter").val(this.value);',
						'placeholder' => 'Choose Date(s)' 
				) 
		));
		
		$this->add(array (
				'name' => 'timecreated',
				'required' => false,
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'type' => 'Application\Form\Element\DateRange',
				'options' => array (
						'label' => 'Date Range',
						'label_attributes' => array (
								'class' => 'sr-only' 
						) 
				),
				'attributes' => array (
						'class' => 'leadFilter',
						'id' => 'timecreatedFilter',
						'onchange' => '$(".timecreatedFilter").val(this.value);',
						'placeholder' => 'Choose Date(s)' 
				) 
		));
		
		$this->add(array (
				'name' => 'reset',
				'type' => 'Zend\Form\Element\Button',
				'options' => array (
						'label' => 'Clear' 
				),
				'attributes' => array (
						'type' => 'reset',
						'id' => 'filterreset',
						'class' => 'btn btn-default' 
				) 
		))
		// 'onclick' => '$("#hiddenFilterForm").reset();'
		
		;
		
		$this->add(array (
				'name' => 'filter',
				'type' => 'Zend\Form\Element\Button',
				'options' => array (
						'label' => 'Filter' 
				),
				'attributes' => array (
						'type' => 'button',
						'id' => 'filtersubmit',
						'class' => 'btn btn-success',
						'onclick' => '$("#hiddenFilterForm").submit();' 
				) 
		));
	
	}

	public function getInputSpecification()
	{
		return [ ];
	}
}

?>