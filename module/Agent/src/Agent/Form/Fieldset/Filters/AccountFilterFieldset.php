<?php

namespace Agent\Form\Fieldset\Filters;

use Zend\InputFilter\InputFilterProviderInterface;
use Application\Form\Fieldset\AbstractFieldset;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Agent\Entity\Filters\AccountFilter;

/**
 *
 * @author arstropica
 *        
 */
class AccountFilterFieldset extends AbstractFieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;

	public function __construct()
	{
		parent::__construct('accountFilter');
	}

	public function init()
	{
		$hydrator = new DoctrineHydrator($this->getObjectManager(), 'Agent\Entity\Filters\AccountFilter');
		
		$this->setAttribute('method', 'post')
			->setHydrator($hydrator)
			->setObject(new AccountFilter());
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'id',
				'required' => false 
		));
		
		$this->add(array (
				'name' => 'mode',
				'type' => 'Zend\Form\Element\Select',
				'required' => false,
				'options' => array (
						'column-size' => 'xs-12 col-sm-4 col-md-3',
						"required" => false,
						"label" => "Filter by: ",
						"label_attributes" => array (
								"class" => "horizontal" 
						),
						"empty_option" => "None",
						"value_options" => array (
								"orphan" => "Unassigned",
								"account" => "Account" 
						) 
				),
				'attributes' => array (
						'class' => 'has-chosen mode filter' 
				) 
		));
		
		$this->add(array (
				'name' => 'account',
				'type' => 'DoctrineModule\Form\Element\ObjectSelect',
				'options' => array (
						'empty_option' => '',
						'column-size' => 'xs-12 col-sm-8 col-md-9',
						'label' => 'Select Account',
						'object_manager' => $this->getObjectManager(),
						'target_class' => 'Account\Entity\Account',
						'property' => 'name',
						'is_method' => true,
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
						'id' => 'accountFilter',
						'class' => 'control filter' 
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
				'mode' => array (
						'required' => false 
				),
				'account' => array (
						'required' => true 
				) 
		);
	}

}

?>