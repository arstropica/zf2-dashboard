<?php

namespace Application\Initializer;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Service\ElasticSearch\SearchableEntityInterface;

/**
 *
 * @author arstropica
 *        
 */
class SearchManagerInitializer implements InitializerInterface {

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\ServiceManager\InitializerInterface::initialize()
	 *
	 */
	public function initialize($instance, ServiceLocatorInterface $serviceLocator)
	{
		if ($instance instanceof SearchableEntityInterface) {
			$instance->setSearchManager($serviceLocator->get('doctrine-searchmanager'));
		}
	}
}

?>