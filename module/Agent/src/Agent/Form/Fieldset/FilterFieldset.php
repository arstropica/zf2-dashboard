<?php

namespace Agent\Form\Fieldset;

use Zend\InputFilter\InputFilterProviderInterface;
use Application\Form\Fieldset\AbstractFieldset;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Agent\Entity\Filter;

/**
 *
 * @author arstropica
 *        
 */
class FilterFieldset extends AbstractFieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;

	public function __construct()
	{
		parent::__construct('filter');
	}

	public function init()
	{
		$hydrator = new DoctrineHydrator($this->getObjectManager(), 'Agent\Entity\Filter');
		
		$this->setAttribute('method', 'post')
			->setHydrator($hydrator)
			->setObject(new Filter());
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'id',
				'required' => false 
		));
		
		$this->add(array (
				'name' => 'accountFilter',
				'type' => 'Agent\Form\Fieldset\Filters\AccountFilterFieldset',
				'options' => array (
						'label' => "1. Filter by Account",
						'use_as_base_fieldset' => false 
				),
				'attributes' => array (
						'id' => 'accountFilter',
						'class' => 'collection-fieldset' 
				) 
		));
		
		$this->add(array (
				'name' => 'dateFilter',
				'type' => 'Agent\Form\Fieldset\Filters\DateFilterFieldset',
				'options' => array (
						'label' => "2. Filter by Date",
						'use_as_base_fieldset' => false 
				),
				'attributes' => array (
						'id' => 'dateFilter',
						'class' => 'collection-fieldset' 
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
				) 
		);
	}
}

?>