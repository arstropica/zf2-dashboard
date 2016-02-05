<?php

namespace Account\Controller;

use LosBase\Controller\AbstractCrudController;
use Zend\Paginator\Paginator;
use Doctrine\ORM\QueryBuilder as Builder;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use LosBase\ORM\Tools\Pagination\Paginator as LosPaginator;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Application\Hydrator\Strategy\DateTimeStrategy;

/**
 *
 * @author arstropica
 *        
 */
class LeadController extends AbstractCrudController {
	
	protected $defaultSort = 'lastsubmitted';
	
	protected $defaultOrder = 'desc';
	
	protected $defaultPageSize = 10;
	
	protected $paginatorRange = 5;
	
	protected $uniqueField = null;
	
	protected $uniqueEntityMessage = null;
	
	protected $successAddMessage = 'The Lead was successfully added.';
	
	protected $successEditMessage = 'The Lead(s) were successfully assigned.';
	
	protected $successDeleteMessage = 'The Lead was successfully deleted.';
	
	protected $errorEditMessage = 'There was a problem assigning your Lead(s).';
	
	protected $errorDeleteMessage = 'There was a problem deleting your Lead.';

	public function listAction()
	{
		$page = $this->getRequest()
			->getQuery('page', 0);
		$limit = $this->getRequest()
			->getQuery('limit', $this->defaultPageSize);
		$sort = $this->getRequest()
			->getQuery('sort', $this->defaultSort);
		$order = $this->getRequest()
			->getQuery('order', $this->defaultOrder);
		
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		if (empty($sort)) {
			$sort = $this->defaultSort;
		}
		
		$offset = $limit * $page - $limit;
		if ($offset < 0) {
			$offset = 0;
		}
		
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()
			->createQueryBuilder();
		$qb->add('select', 'e')
			->add('from', '\Lead\Entity\Lead e')
			->innerJoin('e.account', 'a')
			->andWhere('a.id = :id')
			->orderBy('e.' . $sort, $order)
			->setFirstResult($offset)
			->setMaxResults($limit)
			->setParameter('id', $id);
		
		$qb = $this->handleSearch($qb);
		
		$paginator = new Paginator(new DoctrinePaginator(new LosPaginator($qb, false)));
		$paginator->setDefaultItemCountPerPage($limit);
		$paginator->setCurrentPageNumber($page);
		$paginator->setPageRange($this->paginatorRange);
		
		$ui = [ 
				'table' => [ 
						"referrer" => [ 
								"col" => 6,
								"label" => "Source",
								"sort" => true 
						],
						"lastsubmitted" => [ 
								"col" => 4,
								"label" => "Submitted",
								"sort" => true 
						] 
				] 
		];
		
		$filters = $this->getFilterForm($this->params()
			->fromQuery())
			->remove('account');
		
		return [ 
				'id' => $id,
				'paginator' => $paginator,
				'sort' => $sort,
				'order' => $order,
				'page' => $page,
				'query' => $this->params()
					->fromQuery(),
				'filters' => $filters,
				'ui' => $ui 
		];
	}

	public function getForm($entityClass = null)
	{
		$form = parent::getForm($entityClass);
		
		if ($form) {
			$entityClass = $entityClass ?  : $this->getEntityClass();
			$hydrator = new DoctrineHydrator($this->getEntityManager(), $entityClass);
			$hydrator->addStrategy('timecreated', new DateTimeStrategy());
			$hydrator->addStrategy('lastsubmitted', new DateTimeStrategy());
			$form->setHydrator($hydrator);
			if ($form->has('submit')) {
				$form->get('submit')
					->setLabel('Save');
			}
			if ($form->has('cancelar')) {
				$form->get('cancelar')
					->setLabel('Cancel')
					->setName('cancel');
			}
		}
		return $form;
	}

	protected function getFilterForm($data = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('FormElementManager')
			->get('Lead\Form\FilterForm');
		$form->setInputFilter($form->getInputFilter());
		if ($data) {
			$form->setData($data);
			if (!$form->isValid()) {
				$form->setData(array ());
			}
		}
		return $form;
	}

	public function handleSearch(Builder $qb)
	{
		$query = $this->getRequest()
			->getQuery();
		$filters = [ 
				'daterange',
				'referrer' 
		];
		if ($query) {
			$where = [ ];
			$params = [ ];
			foreach ( $filters as $condition ) {
				if (isset($query [$condition]) && "" !== $query [$condition]) {
					switch ($condition) {
						case 'daterange' :
							list ( $from, $to ) = array_map(function ($d) {
								return date('Y-m-d', strtotime($d));
							}, explode("-", $query [$condition]));
							$where ['from'] = $from;
							$where ['to'] = $to;
							$qb->andWhere($qb->expr()
								->between("e.lastsubmitted", ":from", ":to"));
							// ->between( "e.timecreated", ":from", ":to" ) );
							break;
						case 'referrer' :
							$where ['referrer'] = "%://{$query[$condition]}%";
							$qb->andWhere("e.referrer LIKE :referrer");
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