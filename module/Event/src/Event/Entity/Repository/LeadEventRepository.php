<?php
namespace Event\Entity\Repository;
use Doctrine\ORM\EntityRepository;

/**
 *
 * @author arstropica
 *        
 */
class LeadEventRepository extends EntityRepository
{

	public function getEvents ($id, $params = [], $orderBy = [], $limit = false, $format = 'array')
	{
		$querybuilder = $this->createQueryBuilder('v');
		$querybuilder->select('v')
			->innerJoin('v.event', 'e')
			->innerJoin('v.lead', 'l')
			->where('l.id = :id')
			->setParameter('id', $id);
		
		if ($params) {
			foreach ($params as $field => $value) {
				$chunks = explode('.', $field);
				$querybuilder->andWhere($field . ' = :' . end($chunks))->setParameter(end($chunks), $value);
			}
		}
		
		if ($orderBy) {
			$querybuilder->orderBy(key($orderBy), current($orderBy));
		}
		
		if ($limit) {
			$querybuilder->setMaxResults($limit);
		}
		
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

	public function getEvent ($id, $params = [], $orderBy = [], $format = 'array')
	{
		$result = $this->getEvents($id, $params, $orderBy, 1, $format);
		
		return $result ? current($result) : false;
	}
}

