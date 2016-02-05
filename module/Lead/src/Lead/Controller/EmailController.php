<?php

namespace Lead\Controller;

use Application\Controller\AbstractCrudController;
use Zend\Paginator\Paginator;
use Doctrine\ORM\QueryBuilder as Builder;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use LosBase\ORM\Tools\Pagination\Paginator as LosPaginator;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Zend\Stdlib\ResponseInterface as Response;
use Application\Hydrator\Strategy\DateTimeStrategy;
use Lead\Form\SubmitForm;
use Lead\Entity\Lead;

/**
 *
 * @author arstropica
 *        
 */
class EmailController extends AbstractCrudController {
	
	protected $defaultSort = 'id';
	
	protected $defaultOrder = 'desc';
	
	protected $defaultPageSize = 10;
	
	protected $paginatorRange = 5;
	
	protected $uniqueField = null;
	
	protected $uniqueEntityMessage = null;
	
	protected $successSubmitMessage = 'The Leads(s) were successfully submitted.';
	
	protected $errorEditMessage = 'There was a problem editing your Leads(s).';
	
	protected $errorSubmitMessage = 'There was a problem submitting your Lead(s).';

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
			->innerJoin('e.account', 'account')
			->leftJoin('account.apis', 'apis')
			->where('apis.name = :email')
			->orderBy('e.' . $sort, $order)
			->setFirstResult($offset)
			->setMaxResults($limit)
			->setParameter('email', 'Email');
		
		$qb = $this->handleSearch($qb);
		
		$pager = $this->getPagerForm($limit);
		
		$paginator = new Paginator(new DoctrinePaginator(new LosPaginator($qb, false)));
		$paginator->setDefaultItemCountPerPage($limit);
		$paginator->setCurrentPageNumber($page);
		$paginator->setPageRange($this->paginatorRange);
		
		$ui = [ 
				'table' => [ 
						"description" => [ 
								"col" => 3,
								"label" => "Name",
								"sort" => false 
						],
						"event" => [ 
								"col" => 3,
								"label" => "Latest Email Event",
								"sort" => false 
						],
						"account" => [ 
								"col" => 2,
								"label" => "Account",
								"sort" => false 
						],
						"timecreated" => [ 
								"col" => 2,
								"label" => "Date",
								"sort" => true 
						] 
				] 
		];
		
		$filters = $this->getFilterForm($this->params()
			->fromQuery());
		$post = $this->params()
			->fromPost();
		
		$redirectUrl = $this->url()
			->fromRoute('services/email', [ 
				'action' => 'list' 
		], true);
		
		if (!$pagerAction) {
			$prg = $this->prg($redirectUrl, true);
		} else {
			$prg = false;
		}
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false) {
			$form = $this->getListForm($paginator);
			return [ 
					'paginator' => $paginator,
					'sort' => $sort,
					'order' => $order,
					'page' => $page,
					'pager' => $pager,
					'query' => $this->params()
						->fromQuery(),
					'form' => $form,
					'filters' => $filters,
					'ui' => $ui 
			];
		}
		
		$form = $this->getListForm($paginator, $prg);
		
		if ($prg && isset($prg ['sel'])) {
			$res = true;
			foreach ( $prg ['sel'] as $lead_id => $one ) {
				if ($one) {
					$response = $this->submit($lead_id);
					$res = $response ? $res : false;
				}
			}
			if ($res) {
				$this->flashMessenger()
					->addSuccessMessage($this->getServiceLocator()
					->get('translator')
					->translate($this->successSubmitMessage));
			} else {
				$this->flashMessenger()
					->addErrorMessage($this->getServiceLocator()
					->get('translator')
					->translate($this->errorSubmitMessage));
			}
		}
		return $this->redirect()
			->toRoute('services/email', [ 
				'action' => 'list' 
		], true);
	}

	public function viewAction()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($this->getEntityClass());
		$entity = $objRepository->find($id);
		
		return [ 
				'entity' => $entity 
		];
	}

	public function submitAction()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		if (!$id) {
			return $this->redirect()
				->toRoute('services/email', [ 
					'action' => 'list' 
			], true);
		}
		
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($this->getEntityClass());
		$entity = $objRepository->find($id);
		
		$response = $this->submit($id);
		
		if ($response) {
			$this->flashMessenger()
				->addSuccessMessage($this->getServiceLocator()
				->get('translator')
				->translate($this->successSubmitMessage));
		} else {
			$this->flashMessenger()
				->addErrorMessage($this->getServiceLocator()
				->get('translator')
				->translate($this->errorSubmitMessage));
		}
		
		return [ 
				'entity' => $entity 
		];
	}

	public function exportAction()
	{
		$results = array ();
		$labels = array ();
		$headings = array ();
		
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
			->innerJoin('e.account', 'account')
			->leftJoin('account.apis', 'apis')
			->where('apis.name = :email')
			->orderBy('e.' . $sort, $order)
			->setParameter('email', 'Email');
		$qb = $this->handleSearch($qb);
		
		$entityManager = $this->getEntityManager();
		$hydrator = new DoctrineHydrator($entityManager);
		$leadPrototype = new Lead();
		$leadArray = $hydrator->extract($leadPrototype);
		
		$results = $qb->getQuery()
			->getResult();
		
		$em = $this->getEntityManager();
		$attributeRepository = $em->getRepository("Lead\\Entity\\LeadAttribute");
		
		$attributes = $attributeRepository->getUniqueArray();
		
		$headings = [ 
				'lead' => [ 
						'account' => [ 
								'name' => 'Account' 
						],
						'timecreated' => 'Time Created',
						'referrer' => 'Referrer',
						'ipaddress' => 'IP Address',
						'attributes' => $attributes 
				] 
		];
		
		foreach ( $headings ['lead'] as $property => $field ) {
			$key = $property;
			if (is_array($field)) {
				foreach ( $field as $externalKey => $value ) {
					$key = $property . "[{$externalKey}]";
					$labels [$key] = $value;
				}
			} else {
				$labels [$key] = $field;
			}
		}
		
		$this->exportHeadings = array_values($labels);
		
		return $this->csvExport('Email Lead Report (' . date('Y-m-d') . ').csv', $this->exportHeadings, $results, array (
				$this,
				'extractLead' 
		));
	}

	public function getForm($entityClass = null)
	{
		$form = parent::getForm($entityClass);
		
		if ($form) {
			$entityClass = $entityClass ?  : $this->getEntityClass();
			$hydrator = new DoctrineHydrator($this->getEntityManager(), $entityClass);
			$hydrator->addStrategy('timecreated', new DateTimeStrategy());
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

	protected function getListForm(Paginator $paginator, $data = [])
	{
		$form = new SubmitForm('leademailform');
		
		if ($paginator->count() > 0) {
			// Batch Form
			
			foreach ( $paginator as $entity ) {
				$cbx = new \Zend\Form\Element\Checkbox("sel[" . $entity->getId() . "]");
				$form->add($cbx);
			}
		}
		if ($data) {
			$form->setData($data);
			if (!$form->isValid()) {
				$form->setData(array ());
			}
		}
		return $form;
	}

	protected function getFilterForm($data = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('FormElementManager')
			->get('Lead\Form\FilterForm');
		$form->remove('referrer');
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
				'account' 
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
								->between("e.timecreated", ":from", ":to"));
							break;
						case 'account' :
							switch ($query [$condition]) {
								case 'none' :
									$qb->leftJoin('e.account', 'a');
									$qb->andWhere("a.id IS NULL");
									break;
								default :
									$where ['id'] = $query [$condition];
									$qb->leftJoin('e.account', 'a');
									$qb->andWhere("a.id = :id");
									break;
							}
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

	protected function submit($id)
	{
		// Do something with API Key ???
		$result = $this->getServiceLocator()
			->get('Email\Service\SendMail')
			->send($id);
		
		return isset($result ['error']) ? false : true;
	}

	public function extractLead(Lead $lead)
	{
		$headings = $this->exportHeadings;
		
		$entityManager = $this->getEntityManager();
		$hydrator = new DoctrineHydrator($entityManager);
		$leadArray = $hydrator->extract($lead);
		$output = array_combine($headings, array_pad([ ], count($headings), ""));
		
		foreach ( $headings as $heading ) {
			switch ($heading) {
				case "Account" :
					$account = $lead->getAccount();
					$output [$heading] = $account ? $account->getName() : "N/A";
					break;
				case "Time Created" :
					$time = $lead->getTimecreated();
					if ($time instanceof \DateTime) {
						$time = date_format($time, 'Y-m-d H:i:s');
					}
					$output [$heading] = $time;
					break;
				case "Referrer" :
					$output [$heading] = $lead->getReferrer();
					break;
				case "IP Address" :
					$output [$heading] = $lead->getIpaddress();
					break;
				default :
					$attribute = $lead->findAttribute($heading);
					if (!$attribute) {
						$attributes = $lead->getAttributes(true)
							->filter(function ($attribute) use($heading) {
							$real_attribute = false;
							$attribute_desc = false;
							if ($attribute) {
								$real_attribute = $attribute->getAttribute();
							}
							if ($real_attribute) {
								$attribute_desc = $real_attribute->getAttributeDesc();
							}
							return $attribute_desc == $heading;
						});
						if ($attributes->count() > 0) {
							$attribute = $attributes->first();
						}
					}
					if ($attribute) {
						$output [$heading] = $attribute->getValue();
					}
					break;
			}
		}
		return $output;
	}
}

?>