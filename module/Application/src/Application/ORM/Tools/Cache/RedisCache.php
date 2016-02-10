<?php

namespace Application\ORM\Tools\Cache;

use Doctrine\Common\Cache\RedisCache as BaseCache;

/**
 * Redis Cache Class
 *
 * Restores the deleteByRegex, deleteByPrefix and deleteBySuffix methods
 * in Doctrine 2's RedisCache Driver
 *
 * @author arstropica
 * @link http://github.com/arstropica/
 *      
 */
class RedisCache extends BaseCache {

	/**
	 * Deletes all cache entries matching the given regular expression.
	 *
	 * @param string $regex        	
	 * @return array An array of deleted cache ids
	 */
	public function deleteByRegex($regex)
	{
		$deleted = array ();
		$ids = $this->getIds();
		foreach ( $ids as $id ) {
			if (preg_match($regex, $id)) {
				$deleted [] = $id;
			}
		}
		if ($deleted) {
			foreach ( $deleted as $id ) {
				$this->doDelete($id);
			}
		}
		return $deleted;
	}

	/**
	 * Deletes all cache entries beginning with the given string.
	 *
	 * @param string $prefix        	
	 * @return array An array of deleted cache ids
	 */
	public function deleteByPrefix($prefix)
	{
		$deleted = array ();
		$deleted = $this->getIds($prefix);
		if ($deleted) {
			foreach ( $deleted as $id ) {
				$this->doDelete($id);
			}
		}
		return $deleted;
	}

	/**
	 * Deletes all cache entries ending with the given string.
	 *
	 * @param string $suffix        	
	 * @return array An array of deleted cache ids
	 */
	public function deleteBySuffix($suffix)
	{
		$deleted = array ();
		$ids = $this->getIds();
		foreach ( $ids as $id ) {
			if ($suffix === substr($id, -1 * strlen($suffix))) {
				$deleted [] = $id;
			}
		}
		$this->doDelete($deleted);
		return $deleted;
	}

	/**
	 * Returns an array of cache ids.
	 *
	 * @param string $prefix
	 *        	Optional id prefix
	 * @return array An array of cache ids
	 */
	public function getIds($prefix = null)
	{
		if ($prefix) {
			return $this->getRedis()
				->keys($this->getNamespace() . '\[' . $prefix . '*');
		} else {
			return $this->getRedis()
				->keys($this->getNamespace() . '*');
		}
	}

}

?>