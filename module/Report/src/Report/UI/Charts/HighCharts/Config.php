<?php

namespace Report\UI\Charts\HighCharts;

use Application\Provider\CacheAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Ghunti\HighchartsPHP\HighchartOption;

/**
 * Wrapper class for filtering and provisioning HighCharts Options
 *
 * @author arstropica
 *        
 */
class Config implements ServiceLocatorAwareInterface {
	use CacheAwareTrait, ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var array
	 */
	private $_schema;
	
	/**
	 *
	 * @var string
	 */
	private $_schema_uri = 'http://api.highcharts.com/highcharts/option/dump.json';
	
	/**
	 *
	 * @var array
	 */
	private $_raw_schema;
	
	/**
	 *
	 * @var array
	 */
	private $_values;
	
	/**
	 *
	 * @var HighchartOption
	 */
	private $_config;
	
	/**
	 *
	 * @var array
	 */
	private $_meta = array (
			'fullname',
			'description',
			'context',
			'title',
			'parent',
			'isParent',
			'returnType',
			'defaults' 
	);
	
	/**
	 *
	 * @var \Zend\Cache\Storage\StorageInterface
	 */
	private $cache;

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
	 * Generate HighCharts Options
	 *
	 * @param unknown $values        	
	 * @return \Report\UI\Charts\HighCharts\Config
	 */
	public function generate($values = array())
	{
		$merged = [ ];
		if ($values) {
			$defaults = $this->generateValues();
			$merged = array_intersect_key($values, $defaults);
		}
		$this->_config = $this->generateConfig($merged);
		return $this;
	}

	/**
	 *
	 * Get HighCharts Option
	 *
	 * @return \Ghunti\HighchartsPHP\HighchartOption
	 */
	public function getConfig()
	{
		if (!isset($this->_config)) {
			$this->generate();
		}
		return $this->_config;
	}

	/**
	 * Set HighCharts Options
	 *
	 * @param \Ghunti\HighchartsPHP\HighchartOption $config        	
	 *
	 * @return \Report\UI\Charts\HighCharts\Config
	 */
	private function setConfig($config)
	{
		$this->_config = $config;
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
		if (method_exists($this, "set" . ucwords($offset))) {
			$args = func_get_args();
			array_shift($args);
			return call_user_func_array([ 
					$this,
					"set" . ucwords($offset) 
			], $args);
		}
		if (isset($this->{$offset})) {
			$this->{$offset} = $value;
			return $this;
		}
		$config = $this->getConfig();
		if (isset($config [$offset])) {
			$config [$offset]->offsetSet($offset, $value);
		} else {
			$config [$offset] = $this->generateOption($offset, $value);
		}
		
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
		if (method_exists($this, "get" . ucwords($offset))) {
			$args = func_get_args();
			array_shift($args);
			return call_user_func_array([ 
					$this,
					"get" . ucwords($offset) 
			], $args);
		}
		if (isset($this->{$offset})) {
			return $this->{$offset};
		}
		$config = $this->getConfig();
		if (isset($config [$offset])) {
			return $config [$offset]->offsetGet($offset);
		} else {
			$config [$offset] = $this->generateOption($offset);
			$this->setConfig($config);
		}
		return $config [$offset];
	}

	/**
	 * Get HighCharts Schema array
	 *
	 * @return array
	 */
	protected function getSchema()
	{
		if (!isset($this->_schema)) {
			$cache = $this->getCache();
			if ($cache->hasItem('highcharts-schema')) {
				$this->_schema = $cache->getItem('highcharts-schema');
			} else {
				$schema = $this->parseSchema($this->getRawSchema());
				$cache->setItem('highcharts-schema', $schema);
				$this->_schema = $schema;
			}
		}
		return $this->_schema;
	}

	/**
	 * Fetch HighCharts Schema array from cache or endpoint
	 *
	 * @return boolean|array
	 */
	private function getRawSchema()
	{
		if (!isset($this->_raw_schema)) {
			try {
				$schema_json = file_get_contents($this->_schema_uri);
				$schema_raw = json_decode($schema_json, true);
				$this->_raw_schema = $schema_raw;
			} catch ( \Exception $e ) {
				return false;
			}
		}
		return $this->_raw_schema;
	}

	/**
	 * Restructure HighCharts Schema into recursive format
	 * with meta description
	 *
	 * @param array $schema_raw        	
	 * @return multitype:
	 */
	private function parseSchema($schema_raw)
	{
		$schema = array ();
		foreach ( $schema_raw as $option ) {
			$this->_parse($option, $schema);
		}
		return $schema;
	}

	/**
	 * Clean HighCharts Schema, replacing meta values
	 * with defaults
	 *
	 * @param array|bool $schema        	
	 */
	private function generateValues($schema = false)
	{
		if (!isset($this->_values)) {
			$schema = $schema ?  : $this->getSchema();
			$values = $schema;
			array_walk_recursive($values, /**
			 *
			 * @param array $value        	
			 * @param string $key        	
			 */
			function (&$value, $key) {
				$defaults = null;
				if (is_array($value)) {
					if (isset($value ['_meta']) && (!isset($value ['_meta'] ['isParent'])) || !$value ['_meta'] ['isParent']) {
						if (isset($value ['_meta'] ['defaults'])) {
							$defaults = $value ['_meta'] ['defaults'];
						}
						$value = $defaults;
					}
				}
			});
			$this->_values = $values;
		}
		return $this->_values;
	}

	/**
	 * Generate HighCharts Option object from
	 * options array
	 *
	 * @param array $values        	
	 * @return \Ghunti\HighchartsPHP\HighchartOption
	 */
	private function generateConfig($values)
	{
		$highChartOptions = [ ];
		if ($values && is_array($values)) {
			$highChartOptions = $this->generateOption($values);
		}
		$this->setConfig($highChartOptions);
		return $this->_config;
	}

	/**
	 * Generate new HighChart Option
	 *
	 * @param mixed $values        	
	 *
	 * @return \Ghunti\HighchartsPHP\HighchartOption
	 */
	private function generateOption($values)
	{
		return new HighchartOption($values);
	}

	/**
	 * Recursive Callback for HighCharts schema
	 * reconstruction
	 *
	 * @param array $option        	
	 * @param array $schema        	
	 */
	private function _parse($option, &$schema)
	{
		$fullname = null;
		$defaults = array_combine($this->_meta, array_pad([ ], count($this->_meta), null));
		$args = array_merge($defaults, $option);
		extract($args);
		$paths = explode(".", $fullname);
		krsort($paths);
		$current = &$schema;
		$lvls = count($paths);
		$i = 0;
		while ( count($paths) > 0 ) {
			$var = array_pop($paths);
			if (count($paths) === 0) {
				if (!isset($current [$var])) {
					$current [$var] = [ 
							'_meta' => $args 
					];
				}
			} else {
				if (!isset($current [$var])) {
					$current [$var] = [ ];
				}
				$current = &$current [$var];
			}
			$i++;
			if ($i >= $lvls || count($paths) === 0) {
				break;
			}
		}
	}
}

?>