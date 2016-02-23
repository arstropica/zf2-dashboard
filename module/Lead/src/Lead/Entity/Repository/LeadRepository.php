<?php

namespace Lead\Entity\Repository;

/**
 *
 * @author arstropica
 *        
 */
use Doctrine\ORM\EntityRepository;
use Lead\Entity\Lead;
use JMS\Serializer\Annotation as JMS;

class LeadRepository extends EntityRepository {
	
	CONST CACHE_PREFIX = 'lead-';

	public function getReferrers($number = 0)
	{
		$dql = <<<DQL
		SELECT 
			e.referrer 
		FROM
			\Lead\Entity\Lead e
		GROUP BY 
			e.referrer
		ORDER BY 
			e.referrer
DQL;
		
		$query = $this->getEntityManager()
			->createQuery($dql);
		$query->useQueryCache(true);
		$query->useResultCache(true, 3600, self::CACHE_PREFIX . md5($dql));
		$results = $query->getResult();
		
		// return $results;
		$unique = [ ];
		$filtered = [ ];
		foreach ( $results as $lead ) {
			$referrer = strtolower(parse_url($lead ['referrer'], PHP_URL_HOST));
			if (count(explode(".", $referrer)) > 2) {
				$referrer = implode(".", array_slice(explode(".", $referrer), -2));
			}
			if (!in_array($referrer, $unique)) {
				$unique [] = $referrer;
				$filtered [$referrer] = $referrer;
			}
		}
		return $filtered;
	}

	public function getRecentLeads($number = 10)
	{
		$querybuilder = $this->createQueryBuilder('c');
		$query = $querybuilder->select('c')
			->orderBy('c.timecreated', 'DESC')
			->setMaxResults($number)
			->getQuery();
		
		$query->useResultCache(true, 3600, md5($query->getDQL()))
			->useQueryCache(true);
		$results = $query->getResult();
		
		return $results;
	}

	public function getAvailableLeads($number = 0)
	{
		$entityManager = $this->getEntityManager();
		$eventManager = $entityManager->getEventManager();
		/* @ var $querybuilder \Doctrine\ORM\QueryBuilder */
		$querybuilder = $this->createQueryBuilder('c');
		$querybuilder->select('c')
			->where('c.account IS NULL')
			->orderBy('c.timecreated', 'DESC');
		
		if ($number) {
			$querybuilder->setMaxResults($number);
		}
		$query = $querybuilder->getQuery();
		
		$query->useResultCache(true, 3600, md5($query->getDQL()))
			->useQueryCache(true);
		$results = $query->getResult();
		
		return $results;
	}

	public function getLeads($params = [])
	{

	}

	/**
	 * Finds a single entity by a set of criteria.
	 *
	 * @param array $criteria        	
	 * @param string $alias        	
	 * @param boolean $activeOnly        	
	 *
	 * @return object
	 */
	public function findLeadBy(array $criteria, $alias = 'v', $activeOnly = true)
	{
		$querybuilder = $this->createQueryBuilder('c');
		$query = $querybuilder->select('c')
			->setMaxResults(1);
		
		if ($criteria) {
			self::buildWhere($query, $criteria, $alias);
		}
		
		if ($activeOnly) {
			$query->andWhere('c.active = 1');
		}
		
		$query = $query->getQuery();
		
		$query->useResultCache(true, 3600, md5($query->getDQL()))
			->useQueryCache(true);
		$results = $query->getResult();
		
		return $results ? $results [0] : false;
	}

	public static function buildWhere(&$query, $criteria, $alias)
	{
		$i = 0;
		if (isset($criteria ['and'])) {
			foreach ( $criteria ['and'] as $key => $value ) {
				$fields = [ 
						$alias . $i . '.attribute' => $alias . $i . '.value' 
				];
				$query->innerJoin('c.attributes', $alias . $i);
				$query->andWhere(self::implode_where([ 
						$key => $value 
				], $fields, ' AND '));
				$query->setParameter(':k' . $key, $key);
				$query->setParameter(':v' . $key, $value);
				$i++;
			}
		}
		if (isset($criteria ['or'])) {
			$fields = [ 
					$alias . '.attribute' => $alias . '.value' 
			];
			$query->innerJoin('c.attributes', $alias);
			$query->andWhere(self::implode_where($criteria ['or'], $fields, ' OR '));
			foreach ( $criteria ['or'] as $key => $value ) {
				$query->setParameter(':k' . $key, $key);
				$query->setParameter(':v' . $key, $value);
			}
		}
	}

	public static function implode_where($arr, $fields, $glue = ' AND ', $sep = ' = ')
	{
		$content = [ ];
		if (is_array($arr)) {
			foreach ( $arr as $k => $v ) {
				$content [] = '(' . key($fields) . $sep . ':k' . $k . ' AND ' . current($fields) . $sep . ':v' . $k . ')';
			}
			return implode($glue, $content);
		} else {
			return false;
		}
	}
}