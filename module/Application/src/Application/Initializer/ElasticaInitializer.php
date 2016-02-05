<?php

namespace Application\Initializer;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Service\ElasticaAwareInterface;

/**
 * Initializer for the elastica-aware domain services.
 *
 * @author arstropica
 *        
 */
class ElasticaInitializer implements InitializerInterface {

	/**
	 * Initializer for the elastica-aware domain services.
	 * Properly creates a new elastica client and injects it into related
	 * service.
	 *
	 * @see \Zend\ServiceManager\InitializerInterface::initialize()
	 *
	 */
	public function initialize($instance, ServiceLocatorInterface $serviceLocator)
	{
		if ($instance instanceof ElasticaAwareInterface) {
			$instance->setElasticaClient($serviceLocator->get('elastica-client'));
		}
	}
}
