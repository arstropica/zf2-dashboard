<?php

namespace Lead\Controller;

use LosBase\Controller\ORM\AbstractCrudController;
use Doctrine\ORM\QueryBuilder as Builder;
use Zend\View\Model\JsonModel;

/**
 *
 * @author arstropica
 *        
 */
class AjaxController extends AbstractCrudController {

	public function listAction()
	{
		return new JsonModel([ ]);
	}

	public function nameAction()
	{
		$query = $this->params()
			->fromQuery('query');
		
		$criteria = $this->buildNameCriteria($query);
		
		$limit = $this->params()
			->fromQuery('limit');
		
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()
			->createQueryBuilder();
		$qb->add('select', 'e')
			->add('from', $this->getEntityClass() . ' e');
		
		$qb = $this->handleSearch($qb, $criteria);
		
		$qb->orderBy('e.id');
		
		$results = $qb->getQuery()
			->getResult();
		
		$data = [ ];
		// 'status' => 0
		
		if ($results) {
			// $data ['status'] = 1;
			foreach ( $results as $result ) {
				$data [] = $result->getFullName();
			}
			$data = array_unique($data);
			if ($limit) {
				$data = array_slice($data, 0, $limit);
			}
		
		}
		
		return new JsonModel($data);
	}

	public function buildNameCriteria($name = null)
	{
		$result = [ ];
		if ($name) {
			$terms = explode(" ", $name);
			foreach ( $terms as $i => $term ) {
				switch ($i) {
					case 0 :
						$result ['attributeDesc'] ['First Name'] = $term;
						break;
					case 1 :
						$result ['attributeDesc'] ['Last Name'] = $term;
						break;
				}
			}
		}
		return $result;
	}

	public function handleSearch(Builder $qb, $query = null)
	{
		if (!isset($query)) {
			$query = $this->getRequest()
				->getQuery('query');
		}
		
		$filters = [ 
				'attributeDesc' 
		];
		if ($query) {
			$where = [ ];
			$params = [ ];
			$i = 0;
			foreach ( $filters as $condition ) {
				if (isset($query [$condition])) {
					if (is_array($query [$condition])) {
						foreach ( $query [$condition] as $criteria => $value ) {
							$qb->innerJoin('e.attributes', 'v' . $i);
							$qb->innerJoin('v' . $i . '.attribute', 'a' . $i);
							switch ($condition) {
								case 'attributeDesc' :
									$where ["desc_{$i}"] = "%{$criteria}%";
									$qb->andWhere("a" . $i . ".attributeDesc LIKE :desc_{$i}");
									$where ["value_{$i}"] = "%{$value}%";
									$qb->andWhere("v" . $i . ".value LIKE :value_{$i}");
									$i++;
									break;
							}
						}
					}
				} elseif ("" !== $query [$condition]) {
					$qb->innerJoin('e.attributes', 'v');
					$qb->innerJoin('v.attribute', 'a');
					switch ($condition) {
						case 'attributeDesc' :
							$where ['attributeDesc'] = "%{$query[$condition]}%";
							$qb->andWhere("a.attributeDesc LIKE :attributeDesc");
							break;
					}
				}
			}
			if ($where) {
				foreach ( $where as $key => $value ) {
					$qb->setParameter($key, $value);
				}
			}
		}
		return $qb;
	}
}

?>