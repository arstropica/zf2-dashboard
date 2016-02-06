<?php

namespace Application\Provider;

use Zend\Cache\Storage\StorageInterface;

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
}

?>