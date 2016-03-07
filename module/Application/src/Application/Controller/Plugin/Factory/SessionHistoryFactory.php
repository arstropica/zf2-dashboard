<?php

namespace Application\Controller\Plugin\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Controller\Plugin\SessionHistory;

/**
 *
 * @author arstropica
 *        
 */
class SessionHistoryFactory implements FactoryInterface {

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\ServiceManager\FactoryInterface::createService()
	 *
	 */
	public function createService(ServiceLocatorInterface $pluginManager)
	{
		$serviceManager = $pluginManager->getServiceLocator();
		
		return new SessionHistory($serviceManager);
	}
}

?>