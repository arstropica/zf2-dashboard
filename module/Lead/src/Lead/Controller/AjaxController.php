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

	/**
	 * Lead Name Ajax Search
	 *
	 * @return array
	 */
	public function nameAction()
	{
		$results = [ ];
		$data = [ ];
		
		$query = $this->params()
			->fromQuery('query');
		
		$criteria = $this->buildNameCriteria($query);
		
		if ($criteria) {
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
		}
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
		
		return new JsonModel(array_values($data));
	}

	public function buildNameCriteria($name = null)
	{
		$result = [ ];
		if ($name) {
			$terms = array_filter(explode(" ", $name));
			if ($terms) {
				$result ['attributeDesc'] ['First Name'] = $terms;
				$result ['attributeDesc'] ['Last Name'] = $terms;
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
						foreach ( $query [$condition] as $criteria => $values ) {
							$qb->innerJoin('e.attributes', 'v' . $i);
							$qb->innerJoin('v' . $i . '.attribute', 'a' . $i);
							switch ($condition) {
								case 'attributeDesc' :
									$expr = $qb->expr();
									$andX = $expr->andX();
									$where ["desc_{$i}"] = "%{$criteria}%";
									$andX->add($expr->like("a" . $i . ".attributeDesc", ":desc_{$i}"));
									if (is_array($values)) {
										$j = 0;
										foreach ( $values as $value ) {
											$where ["value_{$j}"] = "%{$value}%";
											$andX->add($expr->like("v" . $i . ".value", ":value_{$j}"));
											$j++;
										}
									} else {
										$where ["value_{$i}"] = "%{$values}%";
										$andX->add($expr->like("v" . $i . ".value", ":value_{$i}"));
									}
									$qb->orWhere($andX);
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