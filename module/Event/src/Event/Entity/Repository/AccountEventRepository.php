<?php
namespace Event\Entity\Repository;
use Doctrine\ORM\EntityRepository;

/**
 *
 * @author arstropica
 *        
 */
class AccountEventRepository extends EntityRepository
{

	/**
	 * Get Associated Account, if any...
	 * 
	 * @param unknown $id        	
	 * @param string $format        	
	 *
	 * @return mixed|boolean
	 */
	public function getAccount ($id, $format = 'array')
	{
		$querybuilder = $this->createQueryBuilder('ae');
		$querybuilder->select('ae')
			->innerJoin('ae.event', 'e')
			->where('e.id = :id')
			->groupBy('ae.account')
			->setParameter('id', $id);
		
		switch ($format) {
			case 'entity':
				return $querybuilder->getQuery()->getResult();
				break;
			case 'array':
				return $querybuilder->getQuery()->getArrayResult();
				break;
			case 'query':
				return $querybuilder;
				break;
			default:
				return $querybuilder->getQuery()->getArrayResult();
				break;
		}
		return false;
	}
}

?>