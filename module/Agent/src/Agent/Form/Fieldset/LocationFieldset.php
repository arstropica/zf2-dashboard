<?php

namespace Agent\Form\Fieldset;

use Application\Form\Fieldset\AbstractFieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Application\Service\ElasticSearch\SearchableEntityInterface;
use Application\Provider\SearchManagerAwareTrait;
use Elastica;
use Elastica\Request;

/**
 *
 * @author arstropica
 *        
 */
class LocationFieldset extends AbstractFieldset implements InputFilterProviderInterface, ServiceLocatorAwareInterface, SearchableEntityInterface {
	use ServiceLocatorAwareTrait, SearchManagerAwareTrait;
	
	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;

	public function __construct()
	{
		parent::__construct('location');
	}

	public function init()
	{
		$this->add(array (
				'options' => array (
						'column-size' => 'xs-12 col-sm-12 col-md-6',
						'label' => 'City:',
						'label_attributes' => array (
								'class' => 'horizontal' 
						) 
				),
				'attributes' => array (
						'class' => 'criterion location city horizontal',
						'placeholder' => 'Enter City' 
				),
				'required' => false,
				'type' => 'text',
				'name' => 'city' 
		));
		
		$this->add(array (
				'options' => array (
						'column-size' => 'xs-8 col-sm-8 col-md-4',
						'label' => 'State(s):',
						'label_attributes' => array (
								'class' => 'horizontal' 
						),
						'empty_option' => 'Choose State(s)',
						'value_options' => $this->getStates() 
				),
				'attributes' => array (
						'class' => 'criterion location city state horizontal',
						'placeholder' => 'Choose State(s)',
						'multiple' => 'multiple' 
				),
				'required' => false,
				'type' => 'select',
				'name' => 'state' 
		));
		
		$this->add(array (
				'options' => array (
						'column-size' => 'xs-4 col-sm-4 col-md-2',
						'label' => 'Zip Code:',
						'label_attributes' => array (
								'class' => 'horizontal' 
						) 
				),
				'attributes' => array (
						'class' => 'criterion location zip horizontal',
						'placeholder' => 'Enter Zip Code',
						'maxlength' => 5,
						'min' => 00000,
						'max' => 99999,
						'step' => 1 
				),
				'required' => false,
				'type' => 'Zend\Form\Element\Number',
				'name' => 'zip' 
		));
		
		$this->add(array (
				'options' => array (
						'column-size' => 'xs-12 col-sm-7 col-md-4',
						'label' => '(Optional) Distance:',
						'label_attributes' => array (
								'class' => 'horizontal' 
						),
						'empty_option' => 'Select Distance',
						'value_options' => $this->getDistance() 
				),
				'attributes' => array (
						'class' => 'criterion location distance horizontal',
						'placeholder' => 'Select Distance' 
				),
				'required' => false,
				'type' => 'select',
				'name' => 'distance' 
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
		return [ ];
	}

	protected function getStates()
	{
		$results = [ ];
		$query = new Elastica\Query();
		$query->setSize(0);
		$agg = new Elastica\Aggregation\Nested('states', 'state');
		$st_terms = new Elastica\Aggregation\Terms('abbrev');
		$st_terms->setField('state.abbrev');
		$st_terms->setOrder('_term', 'asc');
		$st_terms->setSize(0);
		$state_terms = new Elastica\Aggregation\Terms('full');
		$state_terms->setField('state.full');
		$st_terms->addAggregation($state_terms);
		$agg->addAggregation($st_terms);
		$query->addAggregation($agg);
		
		/* @var $elastica_client Elastica\Client */
		$elastica_client = $this->getServiceLocator()
			->getServiceLocator()
			->get('elastica-client');
		
		try {
			/* @var $response \Elastica\Response */
			$response = $elastica_client->request('usgeodb/locality/_search?query_cache=true', Request::GET, $query->toArray());
			$data = $response->getData();
			$aggregations = isset($data ['aggregations'] ['states'] ['abbrev'] ['buckets']) ? $data ['aggregations'] ['states'] ['abbrev'] ['buckets'] : [ ];
			foreach ( $aggregations as $aggregation ) {
				$key = strtoupper($aggregation ['key']);
				$value = ucwords($aggregation ['full'] ['buckets'] [0] ['key']);
				$results [$key] = $value;
			}
		} catch ( \Exception $e ) {
		}
		return $results;
	}

	protected function getDistance()
	{
		$distance_array = array (
				'1',
				'5',
				'10',
				'25',
				'50',
				'100',
				'200',
				'500',
				'1000' 
		);
		return array_combine($distance_array, array_map(function ($m) {
			return "{$m} mile" . ($m == 1 ? "" : "s");
		}, $distance_array));
	}

}
