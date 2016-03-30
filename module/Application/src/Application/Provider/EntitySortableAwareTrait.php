<?php

namespace Application\Provider;

use Doctrine\ORM\EntityManager;

/**
 *
 * @author arstropica
 *        
 */
trait EntitySortableAwareTrait {

	function fetchByOrder($sort, $order = 'asc', $reindex = true, $fixed_id = null, $criteria = [])
	{
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()
			->createQueryBuilder();
		$qb->add('select', 'e')
			->add('from', $this->getEntityClass() . ' e')
			->addSelect('-e.' . $sort . ' AS HIDDEN sort')
			->orderBy('sort', $this->_reverse($order));
		if ($criteria) {
		    if (is_array($criteria)) {
		        foreach ($criteria as $criterion) {
		            $qb->andWhere($criterion);
		        }
		    } else {
		      $qb->where($criteria);
		    }
		}
		$query = $qb->getQuery();
		$results = $query->getArrayResult();
		
		if ($reindex) {
			$reindexed_results = $this->_reindex($results, $sort, $fixed_id);
			$results = $this->_reindex($reindexed_results, $sort);
		}
		return $results;
	}

	protected function _reverse($order)
	{
		return strtolower($order) == 'asc' ? 'desc' : 'asc';
	}

	protected function _move(&$array, $from, $to)
	{
		if (!isset($array [$from]) || $from == $to) {
			return;
		}
		$out = array_splice($array, $from, 1);
		array_splice($array, $to, 0, $out);
	}

	protected function _reindex($array, $sort, $fixed_id = null)
	{
		$reindexed = [ ];
		$new_index = 0;
		$fixed_index = null;
		if ($array) {
			$array = array_values($array);
			for($i = 0; $i < count($array); $i++) {
				$result = $array [$i];
				if (isset($fixed_id) && $fixed_id == $result ['id']) {
					$fixed_index = $result [$sort];
					if ($fixed_index === $new_index) {
						$reindexed [$new_index] = $result;
					} elseif (isset($reindexed [$fixed_index])) {
						$reindexed [$new_index] = $reindexed [$fixed_index];
						$reindexed [$fixed_index] = $result;
					} else {
						$reindexed [$fixed_index] = $result;
					}
				} elseif (isset($reindexed [$new_index])) {
					$new_index++;
					$result [$sort] = $new_index;
					$reindexed [$new_index] = $result;
				} else {
					$result [$sort] = $new_index;
					$reindexed [$new_index] = $result;
				}
				$new_index++;
			}
			$array = $reindexed;
		}
		return $array;
	}

	public abstract function getEntityClass();

	public abstract function setEntityClass($entityClass);

	public abstract function setEntityManager(EntityManager $em);

	public abstract function getEntityManager();
}

?>