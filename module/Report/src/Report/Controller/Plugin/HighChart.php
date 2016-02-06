<?php

namespace Report\Controller\Plugin;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Report\UI\Charts\HighCharts\HighChart as BaseChart;

/**
 *
 * @author arstropica
 *        
 */
class HighChart extends AbstractPlugin implements ServiceLocatorAwareInterface {
	use ServiceLocatorAwareTrait;

	/**
	 * Constructor
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 *
	 * @return void;
	 */
	public function __construct(ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
		$this->setController($serviceLocator->get('ControllerPluginManager')
			->getController());
	}

	/**
	 * Initialize Plugin
	 *
	 * @param string $type        	
	 * @param array $options        	
	 *
	 * @return HighChart
	 */
	public function __invoke($type, $options = array())
	{
		$pluginManager = $this->getServiceLocator();
		$serviceLocator = $pluginManager->getServiceLocator();
		$highChart = new BaseChart($serviceLocator);
		return $highChart->init($type, $options);
	}
}

?>