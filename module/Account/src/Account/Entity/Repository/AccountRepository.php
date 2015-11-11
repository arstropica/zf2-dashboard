<?php
namespace Account\Entity\Repository;

/**
 *
 * @author arstropica
 *        
 */
use Doctrine\ORM\EntityRepository;

class AccountRepository extends EntityRepository
{

	public function getDescriptions ($number = 0)
	{
		$dql = <<<DQL
		SELECT 
			a 
		FROM 
			\Account\Entity\Account a
		ORDER BY a.description ASC
DQL;
		
		$query = $this->getEntityManager()->createQuery($dql);
		if ($number) {
			$query->setMaxResults($number);
		}
		
		$query->useQueryCache(true);
		$query->useResultCache(true, 3600, md5($dql));
		
		$results = $query->getResult();
		$no_account = new \Account\Entity\Account();
		$no_account->setDescription('Unassigned');
		$no_account->setId('none');
		$accounts = [
				'none' => $no_account
		];
		foreach ($results as $result) {
			$accounts[$result->getDescription()] = $result;
		}
		return $accounts;
	}

	public function getNames ($number = 0)
	{
		$dql = <<<DQL
		SELECT 
			a 
		FROM 
			\Account\Entity\Account a
		ORDER BY a.name ASC
DQL;
		
		$query = $this->getEntityManager()->createQuery($dql);
		if ($number) {
			$query->setMaxResults($number);
		}
		
		$query->useQueryCache(true);
		$query->useResultCache(true, 3600, md5($dql));
		
		$results = $query->getResult();
		$no_account = new \Account\Entity\Account();
		$no_account->setName('Unassigned');
		$no_account->setId('none');
		$accounts = [
				'none' => $no_account
		];
		foreach ($results as $result) {
			$accounts[$result->getName()] = $result;
		}
		return $accounts;
	}

	public function getAccounts ()
	{
		$dql = <<<DQL
		SELECT
			a
		FROM
			\Account\Entity\Account a
		ORDER BY a.name ASC
DQL;
		
		$query = $this->getEntityManager()->createQuery($dql);
		
		$query->useQueryCache(true);
		$query->useResultCache(true, 3600, md5($dql));
		
		$results = $query->getResult();
		$accounts = [];
		foreach ($results as $result) {
			$accounts[$result->getId()] = $result;
		}
		return $accounts;
	}

	public function getArrayAccounts ($property = 'name')
	{
		$accounts = $this->getAccounts();
		return array_map(
				function  ($account) use( $property)
				{
					return $account->{'get' . ucfirst($property)}();
				}, array_values($accounts));
	}

	public function getAvailableLeads ($number = 0)
	{
		$dql = <<<DQL
		SELECT
			l
		FROM
			\Lead\Entity\Lead l
		WHERE
			l.account IS NULL
DQL;
		
		$query = $this->getEntityManager()->createQuery($dql);
		
		$query->useQueryCache(true);
		$query->useResultCache(true, 3600, md5($dql));
		
		$results = $query->getResult();
		$leads = [];
		foreach ($results as $result) {
			$leads[$result->getId()] = $result;
		}
		return $leads;
	}
}
