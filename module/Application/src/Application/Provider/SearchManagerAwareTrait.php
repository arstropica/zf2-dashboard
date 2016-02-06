<?php

namespace Application\Provider;

use Doctrine\Search\SearchManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Provides SearchManager Get/Set Implementation
 *
 * @author arstropica
 *        
 */
trait SearchManagerAwareTrait {
	
	/**
	 *
	 * @var SearchManager @\Zend\Form\Annotation\Exclude()
	 */
	protected $searchManager = null;

	/**
	 * Get SearchManager
	 *
	 * @return SearchManager
	 */
	public function getSearchManager()
	{
		if (!isset($this->searchManager)) {
			$searchManager = $this->getServiceLocator()
				->get('doctrine-searchmanager');
			$this->searchManager = $searchManager;
		}
		return $this->searchManager;
	}

	/**
	 * Set SearchManager
	 *
	 * @param SearchManager $searchManager        	
	 */
	public function setSearchManager(SearchManager $searchManager)
	{
		$this->searchManager = $searchManager;
		return $this;
	}

	/**
	 * Set service locator
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return mixed
	 */
	abstract public function setServiceLocator(ServiceLocatorInterface $serviceLocator);

	/**
	 * Get service locator
	 *
	 * @return ServiceLocatorInterface
	 */
	abstract public function getServiceLocator();

}
