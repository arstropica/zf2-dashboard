<?php

namespace Report\Controller\Plugin\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Report\Controller\Plugin\HighChart;

/**
 *
 * @author arstropica
 *        
 */
class HighChartFactory implements FactoryInterface {

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\ServiceManager\FactoryInterface::createService()
	 *
	 * @return HighChart
	 *
	 */
	public function createService(ServiceLocatorInterface $pluginManager)
	{
		$serviceLocator = $pluginManager->getServiceLocator();
		
		return new HighChart($serviceLocator);
	}
}

?>