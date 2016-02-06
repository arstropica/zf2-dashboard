<?php

namespace Lead\Entity\Repository;

/**
 *
 * @author arstropica
 *        
 */
use Doctrine\ORM\EntityRepository;

class LeadAttributeValueRepository extends EntityRepository {
	
	CONST CACHE_PREFIX = 'leadattributevalue-';

	public function getAttributeValues($id, $unique = true, $toArray = true)
	{
		$qb = $this->createQueryBuilder('v')
			->select('v.id, v.value')
			->where('v.attribute = :id')
			->setParameter('id', $id);
		
		if ($unique) {
			$qb->distinct(true);
		}
		
		$dql = $qb->getDQL();
		$query = $qb->getQuery();
		
		$query->useQueryCache(true);
		$query->useResultCache(true, 3600, self::CACHE_PREFIX . md5($dql));
		
		$results = $toArray ? $query->getArrayResult() : $query->getResult();
		return $results;
	}

}
