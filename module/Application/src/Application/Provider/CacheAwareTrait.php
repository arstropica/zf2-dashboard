<?php

namespace Application\Provider;

use Zend\Cache\Storage\StorageInterface;
use Zend\Session\Container;

/**
 * Gets / Sets Cache Implementation
 *
 * @author arstropica
 *        
 */
trait CacheAwareTrait {

	/**
	 * Set Cache Factory
	 *
	 * @param StorageInterface $cache        	
	 *
	 * @return object
	 */
	public function setCache(StorageInterface $cache)
	{
		$this->cache = $cache;
		
		return $this;
	}

	/**
	 * Get Cache Factory
	 *
	 * @return StorageInterface
	 */
	public function getCache()
	{
		if (!isset($this->cache)) {
			$this->cache = $this->getServiceLocator()
				->get('Utils\Cache');
		}
		return $this->cache;
	}

	/**
	 *
	 * Get Session Cache
	 *
	 * @param string $name        	
	 *
	 * @return Container
	 */
	public function getSessionCache($name)
	{
		return new Container($name);
	}
}

?>