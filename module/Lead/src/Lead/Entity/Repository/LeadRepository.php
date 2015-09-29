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
		$querybuilder = $this->createQueryBuilder('c');
		$results = $querybuilder->select('c')
			->groupBy('c.referrer')
			->orderBy('c.referrer', 'ASC')
			->getQuery()
			->getResult();
		$unique = [];
		$filtered = [];
		foreach ($results as $lead) {
			$referrer = parse_url($lead->getReferrer(), PHP_URL_HOST);
			if (! in_array($referrer, $unique)) {
				$unique[] = $referrer;
				$filtered[$referrer] = $lead->setReferrer($referrer);
			}
		}
		return $filtered;
	}


	public function getRecentLeads ($number = 10)
	{
		$querybuilder = $this->createQueryBuilder('c');
		$results = $querybuilder->select('c')
			->orderBy('c.timecreated', 'DESC')
			->setMaxResults($number)
			->getQuery()
			->getResult();
		return $results;
	}
	
	public function getAvailableLeads ($number = 0)
	{
		$entityManager = $this->getEntityManager();
		$eventManager = $entityManager->getEventManager();
		$querybuilder = $this->createQueryBuilder('c');
		return $querybuilder->select('c')
			->where('c.account IS NULL')
			->orderBy('c.timecreated', 'DESC')
			->getQuery()
			->getResult();
	}
}