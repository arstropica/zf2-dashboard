<?php
namespace Application\Provider;
use Doctrine\ORM\EntityManager;

/**
 *
 * @author arstropica
 *        
 */
trait EntitySortableAwareTrait
{

	function fetchByOrder ($sort, $order = 'asc', $reindex = true)
	{
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->add('select', 'e')
			->add('from', $this->getEntityClass() . ' e')
			->addSelect('-e.' . $sort . ' AS HIDDEN sort')
			->orderBy('sort', $this->_reverse($order));
		$query = $qb->getQuery();
		$results = $query->getArrayResult();
		
		if ($reindex) {
			$reindexed_results = [];
			if ($results) {
				foreach ($results as $index => $result) {
					$result[$sort] = $index;
					$reindexed_results[$index] = $result;
				}
			}
			$results = $reindexed_results;
		}
		return $results;
	}

	protected function _reverse ($order)
	{
		return strtolower($order) == 'asc' ? 'desc' : 'asc';
	}

	protected function _move (&$array, $from, $to)
	{
		if (! isset($array[$from]) || $from == $to) {
			return;
		}
		$out = array_splice($array, $from, 1);
		array_splice($array, $to, 0, $out);
	}

	public abstract function getEntityClass ();

	public abstract function setEntityClass ($entityClass);

	public abstract function setEntityManager (EntityManager $em);

	public abstract function getEntityManager ();
}

?>