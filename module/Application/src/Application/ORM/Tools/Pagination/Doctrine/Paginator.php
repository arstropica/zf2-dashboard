<?php

namespace Application\ORM\Tools\Pagination\Doctrine;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\ORM\Query;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

/**
 *
 * @author arstropica
 *        
 */
class Paginator extends DoctrinePaginator implements \Countable, \IteratorAggregate {
	
	/**
	 *
	 * @var Query
	 */
	private $countQuery;
	
	/**
	 *
	 * @var integer
	 */
	private $count;
	
	/**
	 *
	 * @var string
	 */
	private $cache_prefix;

	/**
	 * Constructor.
	 *
	 * @param Query|QueryBuilder $query
	 *        	A Doctrine ORM query or query builder.
	 * @param boolean $fetchJoinCollection
	 *        	Whether the query joins a collection (true by default).
	 * @param boolean $cache
	 *        	Use result cache (true by default).
	 * @param boolean $count
	 *        	Execute count query (true by default).
	 */
	public function __construct($query, $fetchJoinCollection = true, $cached = true, $count = true)
	{
		if ($count) {
			$countQuery = clone ($query);
			$countQuery = $countQuery->select('count(e) as c')
				->setFirstResult(0)
				->setMaxResults(1)
				->getQuery();
			
			$this->countQuery = $countQuery;
		}
		
		$q = $query;
		
		if ($cached) {
			$this->cache_prefix = '';
			if ($query instanceof QueryBuilder) {
				$q = $query->getQuery();
				$entities = $query->getRootEntities();
				if ($entities) {
					$entity = $entities [0];
					$this->cache_prefix = strtolower(substr(strrchr($entity, '\\'), 1) ?  : $entity) . '-';
				}
			}
			$q->useQueryCache(true)
				->useResultCache(true, 3600, $this->cache_prefix . md5($q->getDQL()));
		}
		parent::__construct($q, $fetchJoinCollection);
	}

	public function count()
	{
		if ($this->count === null) {
			if ($this->countQuery) {
				try {
					$res = $this->countQuery->execute();
					$this->count = $res [0] ['c'];
				} catch ( NoResultException $e ) {
					$this->count = 0;
				}
			} else {
				$this->count = parent::count();
			}
		}
		
		return $this->count;
	}

	/**
	 *
	 * @param Query $query        	
	 * @param string $cacheItemKey        	
	 * @return array|bool|mixed|string
	 */
	protected function getCachedResult(Query $query, $cacheItemKey = '', $ttl = 0)
	{
		if (!$cacheItemKey) {
			$cacheItemKey = ($this->cache_prefix ? $this->cache_prefix : get_called_class()) . md5($query->getDQL());
		}
		
		$cache = $this->getEntityManager()
			->getConfiguration()
			->getResultCacheImpl();
		
		// test if item exists in the cache
		if ($cache->contains($cacheItemKey)) {
			// retrieve item from cache
			$items = $cache->fetch($cacheItemKey);
		} else {
			// retrieve item from repository
			$items = $query->getResult();
			// save item to cache
			$cache->save($cacheItemKey, $items, $ttl);
		}
		
		return $items;
	}
}
