<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Event for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Event\Controller;

use Application\Controller\AbstractCrudController;
use Zend\Paginator\Paginator;
use Doctrine\ORM\QueryBuilder as Builder;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Lead\Entity\Lead;
use Account\Entity\Account;
use Event\Entity\Event;

class EventController extends AbstractCrudController {
	
	protected $defaultSort = 'id';
	
	protected $defaultOrder = 'desc';
	
	protected $defaultPageSize = 10;
	
	protected $paginatorRange = 5;
	
	protected $uniqueField = null;

	public function listAction()
	{
		$pagerAction = $this->handlePager();
		$limit = $this->getLimit($this->defaultPageSize);
		// $limit = $this->getRequest()->getQuery('limit',
		// $this->defaultPageSize);
		
		$page = $this->getRequest()
			->getQuery('page', 0);
		$sort = $this->getRequest()
			->getQuery('sort', $this->defaultSort);
		$order = $this->getRequest()
			->getQuery('order', $this->defaultOrder);
		
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
			->add('from', $this->getEntityClass() . ' e')
			->orderBy('e.' . $sort, $order)
			->setFirstResult($offset)
			->setMaxResults($limit);
		
		$qb = $this->handleSearch($qb);
		
		$pager = $this->getPagerForm($limit);
		
		$paginator = new Paginator(new DoctrinePaginator(new ORMPaginator($qb->getQuery(), true)));
		$paginator->setDefaultItemCountPerPage($limit);
		$paginator->setCurrentPageNumber($page);
		$paginator->setPageRange($this->paginatorRange);
		
		$ui = [ 
				'table' => [ 
						"occurred" => [ 
								"col" => 2,
								"label" => "Date",
								"sort" => true 
						],
						"event" => [ 
								"col" => 2,
								"label" => "Event",
								"sort" => false 
						],
						"account" => [ 
								"col" => 3,
								"label" => "Account",
								"sort" => false 
						],
						"message" => [ 
								"col" => 5,
								"label" => "Info",
								"sort" => false 
						] 
				] 
		];
		
		$filters = $this->getFilterForm($this->params()
			->fromQuery());
		$post = $this->params()
			->fromPost();
		
		$redirectUrl = $this->url()
			->fromRoute($this->getActionRoute(), [ ], true);
		
		return [ 
				'paginator' => $paginator,
				'sort' => $sort,
				'order' => $order,
				'page' => $page,
				'pager' => $pager,
				'query' => $this->params()
					->fromQuery(),
				'filters' => $filters,
				'ui' => $ui,
				'history' => $this->setHistory() 
		];
	}

	public function viewAction()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($this->getEntityClass());
		$entity = $objRepository->find($id);
		
		$baseClasses = [ 
				'account' => 'Account',
				'lead' => 'Lead',
				'api' => 'Api',
				'email' => 'EmailApi',
				'error' => 'Error',
				'tenstreet' => 'TenStreetApi' 
		];
		
		$events = [ ];
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()
			->createQueryBuilder();
		$qb->add('select', 'e,' . implode(', ', array_keys($baseClasses)))
			->add('from', $this->getEntityClass() . ' e');
		foreach ( $baseClasses as $alias => $prefix ) {
			$qb->leftJoin("Event\\Entity\\{$prefix}Event", $alias, 'WITH', "e = {$alias}.event");
		}
		$qb->where("e.id = :id")
			->setParameter("id", $id);
		
		$results = $qb->getQuery()
			->getResult();
		reset($baseClasses);
		array_shift($results);
		foreach ( $results as $result ) {
			$events [key($baseClasses)] = $result;
			next($baseClasses);
		}
		
		return [ 
				'entity' => $entity,
				'events' => $events,
				'history' => $this->setHistory() 
		];
	}

	public function exportAction()
	{
		set_time_limit(0);
		$results = array ();
		$labels = array ();
		$headings = array ();
		
		$limit = $this->getLimit($this->defaultPageSize);
		
		$page = $this->getRequest()
			->getQuery('page', 0);
		$sort = $this->getRequest()
			->getQuery('sort', $this->defaultSort);
		$order = $this->getRequest()
			->getQuery('order', $this->defaultOrder);
		
		if (empty($sort)) {
			$sort = $this->defaultSort;
		}
		
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()
			->createQueryBuilder();
		$qb->add('select', 'e')
			->add('from', $this->getEntityClass() . ' e')
			->orderBy('e.' . $sort, $order);
		
		$qb = $this->handleSearch($qb);
		
		$entityManager = $this->getEntityManager();
		$hydrator = new DoctrineHydrator($entityManager);
		$eventPrototype = new Event();
		$eventArray = $hydrator->extract($eventPrototype);
		
		$this->exportHeadings = array_map('ucwords', array_keys($eventArray));
		
		$results = $qb->getQuery()
			->getResult();
		
		return $this->csvExport('Event Log (' . date('Y-m-d') . ').csv', $this->exportHeadings, $results, array (
				$this,
				'extractEvent' 
		));
	}

	protected function getFilterForm($data = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('FormElementManager')
			->get('Event\Form\FilterForm');
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
				'account',
				'event' 
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
							$where ['from'] = $from . ' 00:00:00';
							$where ['to'] = $to . ' 23:59:59';
							$qb->andWhere($qb->expr()
								->between("e.occurred", ":from", ":to"));
							break;
						case 'account' :
							switch ($query [$condition]) {
								default :
									$where ['id'] = $query [$condition];
									$qb->innerJoin('Event\Entity\AccountEvent', 'ae', 'WITH', 'e = ae.event')
										->innerJoin('ae.account', 'account');
									
									$qb->andWhere("account.id = :id");
									break;
							}
							break;
						case 'event' :
							$qb->innerJoin("Event\\Entity\\{$query[$condition]}", 'v', 'WITH', 'e = v.event');
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

	public function extractEvent(Event $event)
	{
		$headings = $this->exportHeadings;
		
		$output = array_combine($headings, array_pad([ ], count($headings), ""));
		
		foreach ( $headings as $heading ) {
			$method_name = 'get' . ucfirst(preg_replace('/^[^\w]/i', '', $heading));
			$method = method_exists($event, $method_name) ? $method_name : false;
			if ($method) {
				switch ($heading) {
					case "Occurred" :
						$time = $event->getOccurred();
						if ($time instanceof \DateTime) {
							$time = date_format($time, 'Y-m-d H:i:s');
						}
						$output [$heading] = $time;
						break;
					default :
						$output [$heading] = $event->{$method}();
						break;
				}
			}
		}
		return $output;
	}

}
