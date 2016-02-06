<?php

namespace Report\UI\Charts\HighCharts;

use Ghunti\HighchartsPHP\Highchart as BaseChart;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 *
 * @author arstropica
 *        
 */
class HighChart implements ServiceLocatorAwareInterface {
	use ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var array
	 */
	protected $_defaults;
	
	/**
	 *
	 * @var Config
	 */
	protected $_config;
	
	/**
	 *
	 * @var array
	 */
	protected $_settings;
	
	protected 

	/**
	 *
	 * @var BaseChart
	 */
	$_chart;

	/**
	 * Constructor
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 */
	public function __construct(ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
	}

	/**
	 * Initialize Chart Options & Type
	 *
	 * @param string $type        	
	 * @param array $options        	
	 *
	 * @return \Report\UI\Charts\HighCharts\HighChart
	 */
	public function init($type, $options = array())
	{
		$defaults = $this->getDefaults();
		$chartOptions = isset($defaults ['options'] ['types'] [$type]) ? $defaults ['options'] ['types'] [$type] : [ ];
		$globalOptions = isset($defaults ['options'] ['global']) ? $defaults ['options'] ['global'] : [ ];
		$this->_settings = array_merge_recursive($globalOptions, $chartOptions, $options);
		return $this;
	}

	/**
	 * Get Chart
	 *
	 * @return \Ghunti\HighchartsPHP\Highchart
	 */
	public function getChart()
	{
		if (!isset($this->_chart)) {
			$chart = new BaseChart();
			$refChart = new \ReflectionClass(get_class($chart));
			$refOptions = $refChart->getProperty('_options');
			$refOptions->setAccessible(true);
			$refOptions->setValue($chart, $this->getConfig()
				->getConfig());
			$this->_chart = $chart;
		}
		return $this->_chart;
	}

	/**
	 * Set Chart Options
	 *
	 * @param array $options        	
	 *
	 * @return \Report\UI\Charts\HighCharts\HighChart
	 */
	public function setOptions($options)
	{
		if ($options && is_array($options)) {
			$this->_settings = array_merge_recursive($this->_settings, $options);
			$this->getConfig(true);
		}
		return $this;
	}

	/**
	 * Megic Setter
	 *
	 * @param string $offset        	
	 * @param mixed $value        	
	 *
	 * @return Config
	 */
	public function __set($offset, $value)
	{
		$this->getChart()
			->__set($offset, $value);
		
		return $this;
	}

	/**
	 * Magic Getter
	 *
	 * @param string $offset        	
	 *
	 * @return multitype
	 */
	public function __get($offset)
	{
		return $this->getChart()
			->__get($offset);
	}

	/**
	 * Get Config Object
	 *
	 * @param string $regen        	
	 *
	 * @return Config
	 */
	protected function getConfig($regen = false)
	{
		if (!isset($this->_config) || $regen) {
			$config = new Config($this->getServiceLocator());
			$this->_config = $config->generate($this->_settings);
		}
		return $this->_config;
	}

	/**
	 * Get Default Settings
	 *
	 * @return array
	 */
	protected function getDefaults()
	{
		if (!isset($this->_defaults)) {
			$defaults = $this->getServiceLocator()
				->get('Config');
			$this->_defaults = isset($defaults ['highcharts']) ? $defaults ['highcharts'] : [ ];
		}
		return $this->_defaults;
	}
}

?>