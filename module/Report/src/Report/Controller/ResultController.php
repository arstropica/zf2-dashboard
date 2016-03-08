<?php

namespace Report\Controller;

use Application\Controller\AbstractCrudController;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Zend\Stdlib\ResponseInterface as Response;
use Lead\Entity\Lead;
use Account\Entity\Account;
use Report\Entity\Report;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\View\Model\JsonModel;
use Report\Entity\Result;

/**
 *
 * @author arstropica
 *        
 */
class ResultController extends AbstractCrudController {
	
	protected $batchSize = 20;
	
	protected $defaultSort = '_score';
	
	protected $defaultOrder = 'desc';
	
	protected $defaultPageSize = 10;
	
	protected $paginatorRange = 5;
	
	protected $uniqueField = null;
	
	protected $uniqueEntityMessage = null;
	
	protected $successAddMessage = 'The Lead(s) were successfully added.';
	
	protected $successEditMessage = 'The Lead(s) were successfully edited.';
	
	protected $successAssignMessage = 'The Lead(s) were successfully assigned.';
	
	protected $successSubmitMessage = 'The Lead(s) were successfully submitted.';
	
	protected $successDeleteMessage = 'The Lead(s) were successfully deleted.';
	
	protected $errorEditMessage = 'There was a problem assigning your Lead(s).';
	
	protected $errorAssignMessage = 'There was a problem assigning your Lead(s).';
	
	protected $errorSubmitMessage = 'There was a problem submitting your Lead(s).';
	
	protected $errorDeleteMessage = 'There was a problem deleting your Lead(s).';
	
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
		
		$objRepository = $this->getEntityManager()
			->getRepository("Report\\Entity\\Report");
		/* @var $report \Report\Entity\Report */
		$report = $objRepository->findOneBy([ 
				'id' => $id 
		]);
		
		if (!$report) {
			return $this->redirect()
				->toRoute('report/list', [ 
					'action' => 'list' 
			], true);
		}
		
		$pager = $this->getPagerForm($limit);
		
		$results = $report ? $report->getResults(false, null, $sort, $order) : [ ];
		
		$paginator = new Paginator(new ArrayAdapter($results));
		$paginator->setCacheEnabled(true);
		$paginator->setDefaultItemCountPerPage($limit);
		$paginator->setCurrentPageNumber($page);
		$paginator->setPageRange($this->paginatorRange);
		
		$ui = [ 
				'table' => [ 
						"_score" => [ 
								"col" => 1,
								"label" => "Score",
								"sort" => true 
						],
						"name" => [ 
								"col" => 3,
								"label" => "Name",
								"sort" => false 
						],
						"account" => [ 
								"col" => 2,
								"label" => "Account",
								"sort" => false 
						],
						"lastsubmitted" => [ 
								"col" => 2,
								"label" => "Submitted",
								"sort" => true 
						],
						"timecreated" => [ 
								"col" => 2,
								"label" => "Date",
								"sort" => true 
						] 
				] 
		];
		
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
					'entity' => $report,
					'paginator' => $paginator,
					'sort' => $sort,
					'order' => $order,
					'page' => $page,
					'pager' => $pager,
					'query' => $this->params()
						->fromQuery(),
					'form' => $form,
					'ui' => $ui,
					'history' => $this->setHistory() 
			];
		}
		
		$form = $this->getListForm($paginator, $prg);
		
		$action = isset($prg ['bulk_action']) ? $prg ['bulk_action'] : false;
		$sel = isset($prg ['sel']) ? array_filter($prg ['sel']) : false;
		$account_id = isset($prg ['account']) ? $prg ['account'] : false;
		if ($action && $prg && $sel && ($account_id || in_array($action, [ 
				'submit',
				'unassign',
				'delete' 
		]))) {
			$res = true;
			$count = 0;
			$total = 0;
			$em = $this->getEntityManager();
			$i = 1;
			try {
				foreach ( array_filter($prg ['sel']) as $lead_id => $one ) {
					if ($one) {
						$res = $this->editLead($lead_id, $account_id, $action) ? $res : false;
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
					case 'submit' :
						$message = str_replace("The", "{$count} out of {$total}", $this->successSubmitMessage);
						break;
					case 'assignSubmit' :
						$message = str_replace("The", "{$count} out of {$total}", $this->successSubmitMessage);
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
					case 'submit' :
						$message = $this->errorSubmitMessage . $message_part . "{$action}ted.";
						break;
					case 'assignSubmit' :
						$message = $this->errorSubmitMessage . $message_part . "submitted.";
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
		return $this->redirect()
			->toRoute($this->getActionRoute(), [ 
				'id' => $id 
		], true);
	}

	public function viewAction()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$lead = $this->getEvent()
			->getRouteMatch()
			->getParam('lead', 0);
		
		$report = $this->params()
			->fromQuery('report');
		
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($this->getEntityClass());
		
		/* @var $report \Report\Entity\Report */
		$report = $objRepository->find($id);
		
		if ($report) {
			$entity = $report->findResult($lead);
			
			return [ 
					'entity' => $entity,
					'report' => $report,
					'history' => $this->setHistory() 
			];
		} else {
			$this->setHistory();
			return $this->getHistoricalRedirect('list');
		}
	}

	public function exportAction()
	{
		set_time_limit(0);
		$results = array ();
		$labels = array ();
		$headings = array ();
		
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$sort = $this->getRequest()
			->getQuery('sort', $this->defaultSort);
		$order = $this->getRequest()
			->getQuery('order', $this->defaultOrder);
		
		if (empty($sort)) {
			$sort = $this->defaultSort;
		}
		
		$objRepository = $this->getEntityManager()
			->getRepository("Report\\Entity\\Report");
		/* @var $report \Report\Entity\Report */
		$report = $objRepository->findOneBy([ 
				'id' => $id 
		]);
		
		$results = $report ? $report->getResults(false, null, $sort) : [ ];
		
		$em = $this->getEntityManager();
		$attributeRepository = $em->getRepository("Lead\\Entity\\LeadAttribute");
		
		$attributes = $this->extractAttributes($results);
		
		$headings = [ 
				'lead' => [ 
						'score' => 'Score',
						'account' => [ 
								'name' => 'Account' 
						],
						'timecreated' => 'Time Created',
						'lastsubmitted' => 'Last Submitted',
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
		
		return $this->csvExport('Search Results (' . date('Y-m-d') . ').csv', $this->exportHeadings, $results, array (
				$this,
				'extractResult' 
		));
	}

	/**
	 *
	 * @param Paginator $paginator        	
	 * @param array $data        	
	 * @return \Lead\Form\ListForm
	 */
	protected function getListForm(Paginator $paginator, $data = [])
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('FormElementManager')
			->get('Lead\Form\ListForm');
		$form->setName('leadbatchform');
		
		if ($paginator->count() > 0) {
			// Batch Form
			
			foreach ( $paginator as $result ) {
				if ($result instanceof Result) {
					$entity = $result->getLead();
					$cbx = new \Zend\Form\Element\Checkbox("sel[" . $entity->getId() . "]");
					$form->add($cbx);
				}
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

	protected function editLead($lead_id, $account_id = false, $action = 'assign', $flush = false)
	{
		$result = true;
		$account = false;
		$actions = [ ];
		$entityClass = 'Lead\Entity\Lead';
		
		$em = $this->getEntityManager();
		$leadRepository = $em->getRepository($entityClass);
		$lead = $leadRepository->find($lead_id);
		
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
		
		if ($actions && $lead instanceof Lead) {
			foreach ( $actions as $action ) {
				$shouldLog = false;
				$this->createServiceEvent()
					->setEntityId($lead_id)
					->setEntityClass($entityClass)
					->setDescription("Lead Edited");
				switch ($action) {
					
					case 'assign' :
						
						if ($lead instanceof Lead && $account instanceof Account) {
							$lead->setAccount($account);
							$account->addLead($lead);
							try {
								$em->persist($account);
								$em->persist($lead);
								if ($flush) {
									$em->flush();
									$em->detach($lead);
								}
								$this->getServiceEvent()
									->setMessage("Lead #{$lead_id} was assigned to " . $account->getName());
								$shouldLog = true;
							} catch ( \Exception $e ) {
								$this->logError($e);
								return false;
							}
						}
						break;
					
					case 'unassign' :
						
						if (($lead instanceof Lead) && (null !== $lead->getAccount())) {
							$account = $lead->getAccount();
							$account->removeLeads([ 
									$lead 
							]);
							$lead->setAccount(null);
							
							try {
								$em->persist($account);
								$em->persist($lead);
								$em->flush();
								if ($flush) {
									$em->flush();
									$em->detach($lead);
								}
								$this->getServiceEvent()
									->setMessage("Lead #{$lead_id} was unassigned");
								$shouldLog = true;
							} catch ( \Exception $e ) {
								$this->logError($e);
								return false;
							}
						}
						break;
					
					case 'submit' :
						
						try {
							$response = $this->forward()
								->dispatch('Lead\Controller\Services', array (
									'action' => 'process',
									'id' => $lead_id 
							));
							$response = $response instanceof JsonModel ? $response->getVariables() : $response;
							if (is_array($response ['data'])) {
								if (isset($response ['data'] ['error'])) {
									foreach ( $response ['data'] ['error'] as $api_error ) {
										if (isset($api_error [0]) && is_string($api_error [0])) {
											$error_msg = $api_error [0];
											throw new \Exception($error_msg, 400);
										}
									}
								}
							}
							if (isset($response ['data'] ['error'], $response ['error']) || empty($response ['data'])) {
								$result = false;
							}
							$shouldLog = false;
						} catch ( \Exception $e ) {
							$this->logError($e);
							return false;
						}
						break;
					
					case 'delete' :
						$this->getServiceEvent()
							->setMessage("Lead #{$lead_id} was deleted.");
						$this->getServiceEvent()
							->setDescription("Lead Deleted");
						$this->logEvent("DeleteAction.post");
						$this->createServiceEvent();
						if ($this->getEntityService()
							->archive($lead)) {
							$shouldLog = true;
						} else {
							$this->logError(new \Exception("Lead #{$lead_id} could not be deleted.", 400));
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

	public function extractResult(Result $result)
	{
		$headings = $this->exportHeadings;
		$lead = $result->getLead();
		
		$entityManager = $this->getEntityManager();
		$hydrator = new DoctrineHydrator($entityManager);
		$leadArray = $hydrator->extract($lead);
		$output = array_combine($headings, array_pad([ ], count($headings), ""));
		
		foreach ( $headings as $heading ) {
			switch ($heading) {
				case "Score" :
					$score = $result->getScore();
					$output [$heading] = $score ? $score : "N/A";
					break;
				case "Account" :
					$account = $lead->getAccount();
					$output [$heading] = $account ? $account->getName() : "N/A";
					break;
				case "Last Submitted" :
					$time = $lead->getLastsubmitted();
					if ($time instanceof \DateTime) {
						$time = date_format($time, 'Y-m-d H:i:s');
					}
					$output [$heading] = $time;
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

	public function getActionRoute($action = null)
	{
		$actionRoute = 'report/application/result';
		if (null === $action) {
			$action = $this->getEvent()
				->getRouteMatch()
				->getParam('action');
		}
		
		return $actionRoute . '/' . $action;
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

?>