<?php

namespace Application\Service;

use Zend\Cache\Storage\StorageInterface;
use Zend\Session\Container;

/**
 * Get / Set Cache Implementation
 *
 * @author arstropica
 *        
 */
interface CacheAwareInterface {

	/**
	 * Set Cache Factory
	 *
	 * @param StorageInterface $cache        	
	 *
	 * @return object
	 */
	public function setCache(StorageInterface $cache);

	/**
	 * Get Cache Factory
	 *
	 * @return StorageInterface
	 */
	public function getCache();

	/**
	 *
	 * Get Session Cache
	 *
	 * @param string $name        	
	 *
	 * @return Container
	 */
	public function getSessionCache($name);

}

?>