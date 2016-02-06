<?php

namespace Lead\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\ServiceLocatorInterface;

class HiddenFilterForm extends Form implements InputFilterAwareInterface {
	
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

	public function __construct()
	{
		parent::__construct('hiddenFilterForm');
	}

	public function init()
	{
		$this->serviceLocator = $this->getFormFactory()
			->getFormElementManager()
			->getServiceLocator();
		
		$this->url = $this->serviceLocator->get('viewhelpermanager')
			->get('url');
		
		$r = $this->serviceLocator->get('Application')
			->getMvcEvent()
			->getRouteMatch();
		
		$rq = $this->serviceLocator->get('Request');
		
		$this->setAttribute('action', $this->url->__invoke($r->getMatchedRouteName(), [ ], [ 
				'query' => $rq->getQuery()
					->toArray() 
		], true));
		
		$this->setAttribute('method', 'GET');
		
		$this->setAttribute('id', 'hiddenFilterForm');
		
		$this->add(array (
				'name' => 'description',
				'type' => 'Zend\Form\Element\Hidden',
				'attributes' => array (
						'class' => 'descriptionFilter' 
				) 
		));
		
		$this->add(array (
				'name' => 'account',
				'type' => 'Zend\Form\Element\Hidden',
				'attributes' => array (
						'class' => 'accountFilter' 
				) 
		));
		$this->add(array (
				'name' => 'referrer',
				'type' => 'Zend\Form\Element\Hidden',
				'attributes' => array (
						'class' => 'referrerFilter' 
				) 
		));
		
		$this->add(array (
				'name' => 'lastsubmitted',
				'type' => 'Zend\Form\Element\Hidden',
				'attributes' => array (
						'class' => 'lastsubmittedFilter' 
				) 
		));
		
		$this->add(array (
				'name' => 'timecreated',
				'type' => 'Zend\Form\Element\Hidden',
				'attributes' => array (
						'class' => 'timecreatedFilter' 
				) 
		));
	}

	public function getInputFilter()
	{
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			
			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}

	public function __sleep()
	{
		return array ();
	}
}
