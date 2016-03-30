<?php

namespace Lead\Controller;

use LosBase\Controller\ORM\AbstractCrudController;
use Doctrine\ORM\QueryBuilder as Builder;
use Zend\View\Model\JsonModel;
use Lead\Entity\Lead;
use Agent\Elastica\Query\LocalityQuery;
use Lead\Entity\LeadAttributeValue;

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
				$data[] = $result->getFullName();
			}
			$data = array_unique($data);
			if ($limit) {
				$data = array_slice($data, 0, $limit);
			}
		
		}
		
		return new JsonModel(array_values($data));
	}

	public function geoAction()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$operation = $this->params()
			->fromQuery('operation', 'info');
		
		$result = [ 
				'outcome' => 0 
		];
		
		$em = $this->getEntityManager();
		
		$objRepository = $em->getRepository($this->getEntityClass());
		
		if ($id) {
			/* @var $lead \Lead\Entity\Lead */
			$lead = $objRepository->find($id);
			
			if ($lead && $lead instanceof Lead) {
				$location = [ ];
				$location['ipaddress'] = $lead->getIpaddress();
				$location['locality'] = $lead->getLocality();
				foreach ( [ 
						'city',
						'state',
						'zip',
						'phone' 
				] as $attribute ) {
					$value = $lead->findAttribute($attribute, true);
					$location[$attribute] = $value ? $value->getValue() : null;
				}
				$result['data']['location'] = $location;
				$missing = array_filter(array_map(function ($x) {
					return empty($x);
				}, array_slice($location, 0, -1)));
				if ($missing) {
					$localityQuery = new LocalityQuery($this->getServiceLocator());
					$locality = $localityQuery->request(array_filter($location));
					if ($locality) {
						switch ($operation) {
							case 'update' :
								$result['outcome'] = $this->updateLeadGeo($lead, $locality) ? 1 : 0;
								break;
							case 'info' :
								$result['outcome'] = 1;
								$result['data']['geo'] = $locality;
								$result['data']['lead'] = $lead->toArray();
						}
					}
				} else {
					$result['outcome'] = 2;
				}
			}
		} else {
			$leads = $em->createQuery("SELECT l.id 
					FROM 
						Lead\\Entity\\Lead l 
							INNER JOIN l.attributes v1 
							INNER JOIN v1.attribute a1 
					WHERE a1.attributeDesc = 'City' 
						AND l.id NOT IN (
						SELECT e.id 
							FROM Lead\\Entity\\Lead e 
								INNER JOIN e.attributes v 
								INNER JOIN v.attribute a 
						WHERE a.attributeDesc = 'State' 
							AND e.locality IS NOT NULL
					)")
				->getScalarResult();
			$result['data']['ids'] = array_map('current', $leads);
			$result['outcome'] = 1;
		}
		return new JsonModel($result);
	}

	public function updateLeadGeo(Lead $lead, $location = [])
	{
		$result = false;
		$update = false;
		
		if ($location && isset($location['_source'])) {
			$locality = $location['_source'];
			
			$em = $this->getEntityManager();
			$attributeRepo = $em->getRepository('Lead\Entity\LeadAttribute');
			// Find missing fields, if any
			$existing = [ ];
			$existing['locality'] = $lead->getLocality();
			foreach ( [ 
					'city',
					'state',
					'zip' 
			] as $attribute ) {
				$value = $lead->findAttribute($attribute, true);
				$existing[$attribute] = $value ? $value->getValue() : null;
			}
			
			foreach ( $existing as $field => $value ) {
				if (empty($value)) {
					switch ($field) {
						case 'locality' :
							$lead->setLocality(implode(",", $locality['latlon']));
							$update = true;
							break;
						default :
							$updated = false;
							switch ($field) {
								case 'state' :
									$updated = $locality[$field]['abbrev'];
									break;
								default :
									$updated = $locality[$field];
									break;
							}
							if ($updated) {
								$attribute_id = $attributeRepo->getIDFromDesc(ucwords($field), $field == 'zip');
								if ($attribute_id) {
									$attribute = $attributeRepo->find($attribute_id);
									if ($attribute) {
										$leadAttributeValue = new LeadAttributeValue();
										
										$leadAttributeValue->setValue($updated);
										$leadAttributeValue->setAttribute($attribute);
										
										$lead->addAttribute($leadAttributeValue);
										$update = true;
									}
								}
							}
							break;
					}
				}
			
			}
			
			if ($update) {
				try {
					$em->merge($lead);
					$em->flush();
					$result = true;
				} catch ( \Exception $e ) {
					$result = false;
				}
			}
		}
		return $result;
	}

	public function buildNameCriteria($name = null)
	{
		$result = [ ];
		if ($name) {
			$terms = array_filter(explode(" ", $name));
			if ($terms) {
				$result['attributeDesc']['First Name'] = $terms;
				$result['attributeDesc']['Last Name'] = $terms;
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
				if (isset($query[$condition])) {
					if (is_array($query[$condition])) {
						foreach ( $query[$condition] as $criteria => $values ) {
							$qb->innerJoin('e.attributes', 'v' . $i);
							$qb->innerJoin('v' . $i . '.attribute', 'a' . $i);
							$qb->andWhere('a' . $i . '= 1');
							switch ($condition) {
								case 'attributeDesc' :
									$expr = $qb->expr();
									$andX = $expr->andX();
									$where["desc_{$i}"] = "%{$criteria}%";
									$andX->add($expr->like("a" . $i . ".attributeDesc", ":desc_{$i}"));
									if (is_array($values)) {
										$j = 0;
										foreach ( $values as $value ) {
											$where["value_{$j}"] = "%{$value}%";
											$andX->add($expr->like("v" . $i . ".value", ":value_{$j}"));
											$j++;
										}
									} else {
										$where["value_{$i}"] = "%{$values}%";
										$andX->add($expr->like("v" . $i . ".value", ":value_{$i}"));
									}
									$qb->orWhere($andX);
									$i++;
									break;
							}
						}
					}
				} elseif ("" !== $query[$condition]) {
					$qb->innerJoin('e.attributes', 'v');
					$qb->innerJoin('v.attribute', 'a');
					switch ($condition) {
						case 'attributeDesc' :
							$where['attributeDesc'] = "%{$query[$condition]}%";
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
		$qb->andWhere('e.active = 1');
		return $qb;
	}
}

?>