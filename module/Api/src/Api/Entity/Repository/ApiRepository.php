<?php
namespace Api\Entity\Repository;

/**
 *
 * @author arstropica
 *        
 */
use Doctrine\ORM\EntityRepository;

class ApiRepository extends EntityRepository
{

	public function findDistinctSettings ($number = 0)
	{
		$dql = <<<DQL
		SELECT 
			a 
		FROM 
			\Api\Entity\ApiSetting a
		GROUP BY 
			a.apiOption
		ORDER BY
			a.apiOption ASC
DQL;
		
		$query = $this->getEntityManager()->createQuery($dql);
		if ($number) {
			$query->setMaxResults($number);
		}
		
		$results = $query->getResult();
		$settings = [];
		foreach ($results as $result) {
			$settings[$result->getApiOption()] = $result;
		}
		return $settings;
	}
}

?>