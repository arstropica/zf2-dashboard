<?php

namespace Application\Service\ElasticSearch\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Elastica\Client;
use Application\Provider\ServiceLocatorAwareTrait;
use Application\Provider\FlashMessengerAwareTrait;

/**
 * Creates the Elastica Client Service
 *
 * @author arstropica
 *        
 */
class ElasticaClientFactory implements FactoryInterface {
	
	use ServiceLocatorAwareTrait, FlashMessengerAwareTrait;

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\ServiceManager\FactoryInterface::createService()
	 *
	 */
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
		
		$config = $serviceLocator->get('Config');
		$clientOptions = isset($config ['elastica'] ['clients'] ['default']) ? $config ['elastica'] ['clients'] ['default'] : array ();
		try {
			$client = new Client($clientOptions);
		} catch ( \Exception $e ) {
			$this->getFlashMessenger()
				->addErrorMessage($e->getMessage());
		}
		return $client;
	}
}
