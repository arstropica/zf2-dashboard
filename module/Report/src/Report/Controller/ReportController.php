<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Report for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Report\Controller;

use Application\Controller\AbstractCrudController;
use Zend\Paginator\Paginator;
use Doctrine\ORM\QueryBuilder as Builder;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Application\ORM\Tools\Pagination\Doctrine\Paginator as FastPaginator;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Application\Hydrator\Strategy\DateTimeStrategy;
use Zend\Stdlib\ResponseInterface as Response;
use Lead\Entity\Lead;
use Account\Entity\Account;
use Report\Entity\Report;
use Doctrine\Common\Collections\ArrayCollection;

class ReportController extends AbstractCrudController {
	
	protected $batchSize = 20;
	
	protected $defaultSort = 'id';
	
	protected $defaultOrder = 'desc';
	
	protected $defaultPageSize = 10;
	
	protected $paginatorRange = 5;
	
	protected $uniqueField = null;
	
	protected $uniqueEntityMessage = null;
	
	protected $successAddMessage = 'The Report(s) were successfully added.';
	
	protected $successEditMessage = 'The Report(s) were successfully edited.';
	
	protected $successAssignMessage = 'The Report(s) were successfully assigned.';
	
	protected $successSubmitMessage = 'The Report(s) were successfully submitted.';
	
	protected $successDeleteMessage = 'The Report(s) were successfully deleted.';
	
	protected $errorEditMessage = 'There was a problem assigning your Report(s).';
	
	protected $errorAssignMessage = 'There was a problem assigning your Report(s).';
	
	protected $errorSubmitMessage = 'There was a problem submitting your Report(s).';
	
	protected $errorDeleteMessage = 'There was a problem deleting your Report(s).';
	
	var $exportHeadings;

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::listAction()
	 */
	public function listAction()
	{
		$pagerAction = $this->handlePager();
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
		
		$paginator = new Paginator(new DoctrinePaginator(new FastPaginator($qb, true)));
		$paginator->setCacheEnabled(true);
		$paginator->setDefaultItemCountPerPage($limit);
		$paginator->setCurrentPageNumber($page);
		$paginator->setPageRange($this->paginatorRange);
		
		$ui = [ 
				'table' => [ 
						"name" => [ 
								"col" => 4,
								"label" => "Name",
								"sort" => false 
						],
						"account" => [ 
								"col" => 2,
								"label" => "Account",
								"sort" => false 
						],
						"leads" => [ 
								"col" => 2,
								"label" => "# Leads",
								"sort" => true 
						],
						"updated" => [ 
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
			->fromRoute($this->getActionRoute(), [ ], true);
		
		if (!$pagerAction) {
			$prg = $this->prg($redirectUrl, true);
		} else {
			$prg = false;
		}
		
		if ($pagerAction) {
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
					'ui' => $ui,
					'history' => $this->setHistory() 
			];
		}
		
		$form = $this->getListForm($paginator, $prg);
		
		$action = $prg ['bulk_action'] ?  : false;
		$sel = isset($prg ['sel']) ? array_filter($prg ['sel']) : false;
		$account_id = isset($prg ['account']) ? $prg ['account'] : false;
		if ($action && $prg && $sel && ($account_id || in_array($action, [ 
				'assign',
				'unassign',
				'delete' 
		]))) {
			$res = true;
			$count = 0;
			$total = 0;
			$em = $this->getEntityManager();
			$i = 1;
			try {
				foreach ( array_filter($prg ['sel']) as $report_id => $one ) {
					if ($one) {
						$res = $this->editReport($report_id, $account_id, $action) ? $res : false;
						$count = $res ? $count + 1 : $count;
						$total++;
						if (($i % $this->batchSize) == 0) {
							$em->flush();
							$em->clear();
						}
						$i++;
					}
				}
				$em->flush();
				$em->clear();
			} catch ( \Exception $e ) {
				$res = false;
			}
			$message = $this->successEditMessage;
			if ($res) {
				
				switch ($action) {
					case 'delete' :
						$message = str_replace("The", "{$count} out of {$total}", $this->successDeleteMessage);
						break;
					case 'unassign' :
					case 'assign' :
						$message = str_replace("The", "{$count} out of {$total}", $this->successAssignMessage);
						break;
				}
				$this->flashMessenger()
					->addSuccessMessage($this->getServiceLocator()
					->get('translator')
					->translate($message));
			} else {
				$message = $this->errorSubmitMessage;
				$message_part = " " . ($total - $count) . " of {$total} Lead(s) were not successfully ";
				
				switch ($action) {
					case 'delete' :
						$message = $this->errorDeleteMessage . $message_part . "{$action}ed.";
						break;
					case 'unassign' :
					case 'assign' :
						$message = $this->errorAssignMessage . $message_part . "{$action}ed.";
						break;
				}
				$this->flashMessenger()
					->addErrorMessage($this->getServiceLocator()
					->get('translator')
					->translate($message));
			}
		} else {
			$message = "One or more required fields are missing.";
			$this->flashMessenger()
				->addErrorMessage($this->getServiceLocator()
				->get('translator')
				->translate($message));
		}
		
		return $this->getHistoricalRedirect('list', true);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::viewAction()
	 */
	public function viewAction()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($this->getEntityClass());
		$entity = $objRepository->find($id);
		
		return [ 
				'entity' => $entity,
				'history' => $this->setHistory() 
		];
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::addAction()
	 */
	public function addAction()
	{
		if (method_exists($this, 'getAddForm')) {
			$form = $this->getAddForm();
		} else {
			$form = $this->getForm();
		}
		
		$request = $this->getRequest();
		$classe = $this->getEntityClass();
		
		/* @var $entity \Report\Entity\Report */
		$entity = new $classe();
		
		$account = $this->params()
			->fromQuery('account');
		if ($account) {
			$objRepository = $this->getEntityManager()
				->getRepository('Account\Entity\Account');
			$aEntity = $objRepository->findOneBy([ 
					'id' => $account 
			]);
			if ($aEntity) {
				$entity->setAccount($aEntity);
				$entity->setName($aEntity->getName() . ' Report');
			}
		}
		
		$this->getEventManager()
			->trigger('getForm', $this, [ 
				'form' => $form,
				'entityClass' => $this->getEntityClass(),
				'id' => 0,
				'entity' => $entity 
		]);
		
		$form->bind($entity);
		
		$redirectUrl = $this->url()
			->fromRoute($this->getActionRoute(), [ ], true);
		$prg = $this->fileprg($form, $redirectUrl, true);
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false || !$form->isValid()) {
			if ($prg && !$form->isValid()) {
				$message = "You have invalid Form Entries.";
				$this->flashMessenger()
					->addErrorMessage($message);
				$messages = $form->getMessages();
				if ($messages) {
					$this->flashMessenger()
						->addErrorMessage($this->formatFormMessages($form, ". <br>\n", true));
				}
			}
			return [ 
					'entityForm' => $form,
					'entity' => $entity,
					'history' => $this->setHistory() 
			];
		}
		
		$entity = $this->setRelationships($entity, $prg);
		
		$this->createServiceEvent()
			->setEntityClass($this->getEntityClass())
			->setDescription("Report Created");
		
		$savedEntity = $this->getEntityService()
			->save($form, $entity);
		
		if ($savedEntity && $savedEntity instanceof Report) {
			$id = $savedEntity->getId();
			$name = $savedEntity->getName();
			$this->getServiceEvent()
				->setEntityId($id)
				->setMessage("Report: \"{$name}\" was created.");
			
			$this->logEvent("AddAction.post");
		} else {
			return [ 
					'entityForm' => $form,
					'entity' => $entity,
					'history' => $this->getHistory() 
			];
		}
		
		$this->flashMessenger()
			->addSuccessMessage($this->getServiceLocator()
			->get('translator')
			->translate($this->successAddMessage));
		
		if ($this->needAddOther($form)) {
			$action = 'add';
		} else {
			$action = 'list';
		}
		
		return $this->getHistoricalRedirect('list');
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::editAction()
	 */
	public function editAction()
	{
		if (method_exists($this, 'getEditForm')) {
			$form = $this->getEditForm();
		} else {
			$form = $this->getForm();
		}
		
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$form->add([ 
				'type' => 'Zend\Form\Element\Hidden',
				'name' => 'id',
				'attributes' => [ 
						'id' => 'id',
						'value' => $id 
				],
				'filters' => [ 
						[ 
								'name' => 'Int' 
						] 
				],
				'validators' => [ 
						[ 
								'name' => 'Digits' 
						] 
				] 
		]);
		
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($this->getEntityClass());
		$entity = $objRepository->find($id);
		$name = $entity->getName();
		$account_old = $entity->getAccount();
		$account_old_name = $account_old instanceof Account ? $account_old->getName() : false;
		$account_old_id = $account_old instanceof Account ? $account_old->getId() : false;
		
		$this->getEventManager()
			->trigger('getForm', $this, [ 
				'form' => $form,
				'entityClass' => $this->getEntityClass(),
				'id' => $id,
				'entity' => $entity 
		]);
		
		$form->bind($entity);
		
		$redirectUrl = $this->url()
			->fromRoute($this->getActionRoute(), [ ], true);
		$prg = $this->fileprg($form, $redirectUrl, true);
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false || !$form->isValid()) {
			if ($prg && !$form->isValid()) {
				$message = "You have invalid Form Entries.";
				$this->flashMessenger()
					->addErrorMessage($message);
				$messages = $form->getMessages();
				if ($messages) {
					$this->flashMessenger()
						->addErrorMessage($this->formatFormMessages($form, ". <br>\n", true));
				}
			}
			
			return [ 
					'entityForm' => $form,
					'entity' => $entity,
					'history' => $this->setHistory() 
			];
		}
		
		$entity = $this->setRelationships($entity, $prg);
		
		$this->getEventManager()
			->trigger('edit', $this, [ 
				'form' => $form,
				'entityClass' => $this->getEntityClass(),
				'id' => $id,
				'entity' => $entity 
		]);
		
		$savedEntity = $this->getEntityService()
			->save($form, $entity);
		
		if ($savedEntity && $savedEntity instanceof Report) {
			$account = $savedEntity->getAccount();
			$account_id = $account instanceof Account ? $account->getId() : false;
			$this->createServiceEvent()
				->setEntityId($id)
				->setEntityClass($this->getEntityClass())
				->setDescription("Report Edited");
			
			$this->getServiceEvent()
				->setMessage("Report: \"{$name}\" was edited.");
			
			$this->logEvent("EditAction.post");
			if ($account_id != $account_old_id) {
				$this->createServiceEvent()
					->setEntityId($id)
					->setEntityClass($this->getEntityClass())
					->setDescription("Report Edited");
				
				$this->getServiceEvent()
					->setMessage("Report: \"{$name}\" was " . ($account ? "assigned to " . $account->getName() : "unassigned") . ($account_old_name ? " from " . $account_old_name : "") . ".");
				
				$this->logEvent("EditAction.post");
			}
		} else {
			return [ 
					'entityForm' => $form,
					'entity' => $entity,
					'history' => $this->getHistory() 
			];
		}
		
		$this->flashMessenger()
			->addSuccessMessage($this->getServiceLocator()
			->get('translator')
			->translate($this->successEditMessage));
		
		return $this->getHistoricalRedirect('list');
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::deleteAction()
	 */
	public function deleteAction()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$redirectUrl = $this->url()
			->fromRoute($this->getActionRoute(), [ ], true);
		$prg = $this->prg($redirectUrl, true);
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false) {
			$em = $this->getEntityManager();
			$objRepository = $em->getRepository($this->getEntityClass());
			$entity = $objRepository->find($id);
			
			return [ 
					'entity' => $entity,
					'history' => $this->setHistory() 
			];
		}
		
		$post = $prg;
		
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($this->getEntityClass());
		$entity = $objRepository->find($id);
		$name = $entity->getName();
		
		if ($this->validateDelete($post)) {
			if (null !== $entity->getAccount()) {
				$entity->setAccount(null);
				$this->createServiceEvent()
					->setEntityId($id)
					->setEntityClass($this->getEntityClass())
					->setDescription("Report Edited")
					->setMessage("Report: \"{$name}\" was unassigned.");
				try {
					$em->persist($entity);
					$em->flush();
					$this->getServiceEvent()
						->setMessage("Report: \"{$name}\" was unassigned");
					$this->logEvent("EditAction.post");
				} catch ( \Exception $e ) {
					$this->logError($e);
					$this->flashMessenger()
						->addErrorMessage($this->getServiceLocator()
						->get('translator')
						->translate($this->errorDeleteMessage));
					return false;
				}
			}
			if ($this->getEntityService()
				->archive($entity)) {
				$this->createServiceEvent()
					->setEntityId($id)
					->setEntityClass($this->getEntityClass())
					->setDescription("Report Deleted")
					->setMessage("Report: \"{$name}\" was deleted.");
				
				$this->logEvent("DeleteAction.post");
				
				$this->flashMessenger()
					->addSuccessMessage($this->getServiceLocator()
					->get('translator')
					->translate($this->successDeleteMessage));
				
				return $this->getHistoricalRedirect('list');
			}
		}
		
		$this->flashMessenger()
			->addErrorMessage($this->getServiceLocator()
			->get('translator')
			->translate($this->errorDeleteMessage));
		
		return [ 
				'entity' => $entity,
				'history' => $this->setHistory() 
		];
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::getForm()
	 *
	 * @return /Zend/Form/Form
	 */
	public function getForm($entityClass = null)
	{
		$form = parent::getForm($entityClass);
		
		if ($form) {
			$entityClass = $entityClass ?  : $this->getEntityClass();
			$hydrator = new DoctrineHydrator($this->getEntityManager(), $entityClass);
			$hydrator->addStrategy('updated', new DateTimeStrategy());
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
		$sl = $this->getServiceLocator();
		$form = $sl->get('FormElementManager')
			->get('Report\Form\ListForm');
		$form->setName('reportbatchform');
		
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
			->get('Report\Form\FilterForm');
		$form->setInputFilter($form->getInputFilter());
		if ($data) {
			$form->setData($data);
			if (!$form->isValid()) {
				$form->setData(array ());
			}
		}
		return $form;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::getAddForm()
	 *
	 * @return \Report\Form\AddForm
	 */
	protected function getAddForm($data = array())
	{
		$sl = $this->getServiceLocator();
		/* @var $form \Report\Form\AddForm */
		$form = $sl->get('Report\Form\AddFormFactory');
		$form->get('cancel')
			->setAttributes([ 
				'onclick' => 'top.location=\'' . $this->url()
					->fromRoute($this->getActionRoute('list')) . '\'' 
		]);
		
		$request = $this->getRequest();
		
		if ($data) {
			$form->setData($data);
			if (!$form->isValid()) {
				$form->setData(array ());
			}
		}
		
		return $form;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::getEditForm()
	 *
	 * @return \Report\Form\EditForm
	 */
	protected function getEditForm($data = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('Report\Form\EditFormFactory');
		$form->get('cancel')
			->setAttributes([ 
				'onclick' => 'top.location=\'' . $this->url()
					->fromRoute($this->getActionRoute('list')) . '\'' 
		]);
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

	protected function editReport($report_id, $account_id = false, $action = 'assign', $flush = false)
	{
		$result = true;
		$account = false;
		$actions = [ ];
		
		$em = $this->getEntityManager();
		$reportRepository = $em->getRepository($this->getEntityClass());
		$report = $reportRepository->find($report_id);
		$report_name = $report ? $report->getName() : $report_id;
		
		if ($account_id) {
			$accountRepository = $em->getRepository('Account\Entity\Account');
			$account = $accountRepository->find($account_id);
		}
		
		switch ($action) {
			case 'assign' :
				$actions [] = 'assign';
				break;
			case 'unassign' :
				$actions [] = 'unassign';
				break;
			case 'delete' :
				$actions [] = 'unassign';
				$actions [] = 'delete';
				break;
			case 'submit' :
				$actions [] = 'submit';
				break;
			case 'assignSubmit' :
				$actions [] = 'assign';
				$actions [] = 'submit';
				break;
		}
		
		if ($actions && $report instanceof Report) {
			foreach ( $actions as $action ) {
				$shouldLog = false;
				$this->createServiceEvent()
					->setEntityId($report_id)
					->setEntityClass($this->getEntityClass())
					->setDescription("Report Edited");
				switch ($action) {
					
					case 'assign' :
						
						if ($report instanceof Report && $account instanceof Account) {
							$report->setAccount($account);
							try {
								$em->persist($account);
								$em->persist($report);
								if ($flush) {
									$em->flush();
									$em->detach($report);
								}
								$this->getServiceEvent()
									->setMessage("Report: \"{$report_name}\" was assigned to " . $account->getName());
								$shouldLog = true;
							} catch ( \Exception $e ) {
								$this->logError($e);
								return false;
							}
						}
						break;
					
					case 'unassign' :
						
						if (($report instanceof Report) && (null !== $report->getAccount())) {
							$account = $report->getAccount();
							$account->removeReports(new ArrayCollection([ 
									$report 
							]));
							
							try {
								$em->persist($account);
								$em->persist($report);
								$em->flush();
								if ($flush) {
									$em->flush();
									$em->detach($report);
								}
								$this->getServiceEvent()
									->setMessage("Report: \"{$report_name}\" was unassigned");
								$shouldLog = true;
							} catch ( \Exception $e ) {
								$this->logError($e);
								return false;
							}
						}
						break;
					
					case 'delete' :
						$this->getServiceEvent()
							->setMessage("Report: \"{$report_name}\" was deleted.");
						$this->getServiceEvent()
							->setDescription("Report Deleted");
						$this->logEvent("DeleteAction.post");
						$this->createServiceEvent();
						if ($this->getEntityService()
							->archive($report)) {
							$shouldLog = true;
						} else {
							$this->logError(new \Exception("Report: \"{$report_name}\" could not be deleted.", 400));
							return false;
						}
						break;
				}
				if ($shouldLog && $this->getServiceEvent()
					->getDescription()) {
					$this->logEvent("EditAction.post");
				}
			}
		}
		return $result ? true : false;
	}

	/**
	 *
	 * @param Report $report        	
	 * @param array $post        	
	 */
	protected function setRelationships(Report $report, $post)
	{
		if (isset($post ['agent'] ['criteria'])) {
			$criteria = $post ['agent'] ['criteria'];
			foreach ( $criteria as $i => $criterion ) {
				$_criterion = $report->getAgent()
					->getCriteria(true)
					->get($i);
				if ($_criterion && isset($criterion ['relationship'])) {
					$relationship_id = $criterion ['relationship'];
					$_relationship = $this->getEntityManager()
						->getRepository("Agent\\Entity\\Relationship")
						->findOneBy([ 
							'id' => $relationship_id 
					]);
					if ($_relationship) {
						$_relationship->setId($relationship_id);
						$_criterion->setRelationship($_relationship);
					}
					$report->getAgent()
						->getCriteria(true)
						->set($i, $_criterion);
				}
			}
		}
		return $report;
	}

	protected function logEvent($event)
	{
		$this->getEventManager()
			->trigger($event, $this->getServiceEvent());
	}

	protected function logError(\Exception $e, $result = [])
	{
		$this->getServiceEvent()
			->setIsError(true);
		$this->getServiceEvent()
			->setMessage($e->getMessage());
		if ($result) {
			$this->getServiceEvent()
				->setResult(print_r($result, true));
		} else {
			$this->getServiceEvent()
				->setResult($e->getTraceAsString());
		}
		$this->logEvent('RuntimeError');
	}
}
    