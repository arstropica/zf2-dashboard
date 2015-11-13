<?php
namespace Lead\Entity\Repository;

/**
 *
 * @author arstropica
 *        
 */
use Doctrine\ORM\EntityRepository;
use Lead\Entity\Lead;

class LeadRepository extends EntityRepository
{

	public function getReferrers ($number = 0)
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
		
		$query = $this->getEntityManager()->createQuery($dql);
		$query->useQueryCache(true);
		$query->useResultCache(true, 3600, md5($dql));
		$results = $query->getResult();
		
		// return $results;
		$unique = [];
		$filtered = [];
		foreach ($results as $lead) {
			$referrer = parse_url($lead['referrer'], PHP_URL_HOST);
			if (! in_array($referrer, $unique)) {
				$unique[] = $referrer;
				$filtered[$referrer] = $referrer;
			}
		}
		return $filtered;
	}

	public function getRecentLeads ($number = 10)
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

	public function getAvailableLeads ($number = 0)
	{
		$entityManager = $this->getEntityManager();
		$eventManager = $entityManager->getEventManager();
		$querybuilder = $this->createQueryBuilder('c');
		$query = $querybuilder->select('c')
			->where('c.account IS NULL')
			->orderBy('c.timecreated', 'DESC')
			->getQuery();
		
		$query->useResultCache(true, 3600, md5($query->getDQL()))
			->useQueryCache(true);
		$results = $query->getResult();
		
		return $results;
	}
}