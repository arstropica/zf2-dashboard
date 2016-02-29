<?php

namespace Agent\Form\Fieldset\Filters;

use Zend\InputFilter\InputFilterProviderInterface;
use Application\Form\Fieldset\AbstractFieldset;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Agent\Entity\Filters\DateFilter;

/**
 *
 * @author arstropica
 *        
 */
class DateFilterFieldset extends AbstractFieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;

	public function __construct()
	{
		parent::__construct('dateFilter');
	}

	public function init()
	{
		$hydrator = new DoctrineHydrator($this->getObjectManager(), 'Agent\Entity\Filters\DateFilter');
		
		$this->setAttribute('method', 'post')
			->setHydrator($hydrator)
			->setObject(new DateFilter());
		
		$this->add(array (
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'id',
				'required' => false 
		));
		
		$this->add(array (
				'name' => 'mode',
				'type' => 'Zend\Form\Element\Select',
				'required' => false,
				"required" => "false",
				'options' => array (
						'column-size' => 'xs-12 col-sm-4 col-md-3',
						"label" => "Filter by: ",
						"empty_option" => "None",
						"value_options" => array (
								"1" => "Today",
								"7" => "Last 7 Days",
								"30" => "Last 30 Days",
								"month" => "This Month",
								"lmonth" => "Last Month",
								"year" => "This Year",
								"timecreated" => "Fixed Date" 
						) 
				),
				'attributes' => array (
						'class' => 'has-chosen mode filter' 
				) 
		));
		
		$this->add(array (
				'name' => 'timecreated',
				'allow_empty' => true,
				'filters' => array (
						array (
								'name' => 'Zend\Filter\StringTrim' 
						) 
				),
				'type' => 'Application\Form\Element\DateRange',
				'options' => array (
						'column-size' => 'xs-12 col-sm-8 col-md-9',
						'label' => 'Select Fixed Date Range: ',
						'label_attributes' => array (
								'class' => '' 
						) 
				),
				'attributes' => array (
						'class' => 'timecreated daterange control filter',
						'id' => 'timecreated' 
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
				'fixed' => array (
						'required' => true 
				) 
		);
	}

}

?>