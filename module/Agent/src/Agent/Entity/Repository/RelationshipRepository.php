<?php

namespace Agent\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Agent\Entity\Relationship;

class RelationshipRepository extends EntityRepository {
	
	CONST CACHE_PREFIX = 'relationship-';

	public function generateOptions()
	{
		$querybuilder = $this->createQueryBuilder('c');
		$query = $querybuilder->select('c')
			->getQuery();
		
		$query->useResultCache(true, 3600, self::CACHE_PREFIX . md5($query->getDQL()))
			->useQueryCache(true);
		/* @var $results Relationship[] */
		$results = $query->getResult();
		
		foreach ( $results as &$relationship ) {
			$description = $relationship->getDescription() . " ( " . $relationship->getSymbol() . " )";
			$relationship->setDescription($description);
		}
		
		return $results;
	}

	public function getLabels()
	{
		$querybuilder = $this->createQueryBuilder('c');
		$query = $querybuilder->select('c')
			->getQuery();
		
		$query->useResultCache(true, 3600, self::CACHE_PREFIX . md5($query->getDQL()))
			->useQueryCache(true);
		
		/* @var $results Relationship[] */
		$results = $query->getResult();
		
		foreach ( $results as &$relationship ) {
			$label = $relationship->getLabel() . " ( " . $relationship->getSymbol() . " )";
			$relationship->setLabel($label);
		}
		
		return $results;
	}
}

?>