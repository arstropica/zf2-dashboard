<?php

namespace WebWorks\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use WebWorks\Service\ImportXMLService;

/**
 *
 * @author arstropica
 *        
 */
class ImportXMLServiceFactory implements FactoryInterface {

	public function createService(ServiceLocatorInterface $service)
	{
		try {
			return new ImportXMLService($service);
		} catch ( \Exception $e ) {
			return false;
		}
	}
}

?>