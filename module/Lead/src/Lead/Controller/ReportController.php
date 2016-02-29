<?php

namespace Lead\Controller;

use Application\Controller\AbstractCrudController;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Zend\Stdlib\ResponseInterface as Response;
use Lead\Entity\Lead;
use Account\Entity\Account;
use Report\Entity\Report;
use Zend\Paginator\Adapter\ArrayAdapter;
use Application\Hydrator\Strategy\DateTimeStrategy;
use Agent\Entity\Agent;
use Application\Provider\CacheAwareTrait;
use Report\Entity\Result;

/**
 *
 * @author arstropica
 *        
 */
class ReportController extends AbstractCrudController {
	
	use CacheAwareTrait;
	
	protected $defaultSort = '_score';
	
	protected $defaultOrder = 'desc';
	
	protected $defaultPageSize = 10;
	
	protected $paginatorRange = 5;
	
	protected $uniqueField = null;
	
	protected $uniqueEntityMessage = null;
	
	var $exportHeadings;

	public function searchAction()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
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
		
		if (method_exists($this, 'getSearchForm')) {
			$form = $this->getSearchForm();
		} else {
			$form = $this->getForm();
		}
		
		if ($id) {
			$form->get('id')
				->setValue($id);
		}
		
		$request = $this->getRequest();
		$classe = $this->getEntityClass();
		
		/* @var $report \Report\Entity\Report */
		$report = new $classe();
		$report->setServiceLocator($this->getServiceLocator());
		
		$account = $this->params()
			->fromQuery('account');
		if ($account) {
			$objRepository = $this->getEntityManager()
				->getRepository('Account\Entity\Account');
			$aEntity = $objRepository->findOneBy([ 
					'id' => $account 
			]);
			if ($aEntity) {
				$report->setAccount($aEntity);
				$report->setName($aEntity->getName() . ' Report');
			}
		}
		
		$this->getEventManager()
			->trigger('getForm', $this, [ 
				'form' => $form,
				'entityClass' => $this->getEntityClass(),
				'id' => 0,
				'entity' => $report 
		]);
		
		$form->bind($report);
		
		$pager = $this->getPagerForm($limit);
		
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
		
		$redirectUrl = $this->url()
			->fromRoute($this->getActionRoute(), [ ], [ 
				'query' => $this->params()
					->fromQuery() 
		], true);
		
		if (!$pagerAction) {
			$prg = $this->fileprg($form, $redirectUrl, true);
		} else {
			$prg = false;
		}
		
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
			} elseif ($id) {
				// Retrieve Session-Cached data
				$cachedData = $this->getCachedParams($id);
				if ($cachedData) {
					$form->setData($cachedData);
					if ($form->isValid()) {
						$form->get('id')
							->setValue($id);
						$prg = $cachedData;
					}
				}
			}
		
		}
		
		// Set Session-Cached data
		if ($prg) {
			$_id = md5(serialize($prg));
			$report->setId($_id);
			$form->get('id')
				->setValue($_id);
			$this->setCachedParams($_id, $prg);
			if ($_id != $id) {
				$this->redirect()
					->toRoute($this->getActionRoute(), [ 
						'id' => $_id 
				], [ 
						'query' => $this->params()
							->fromQuery() 
				], true);
			}
		}
		
		if ($report) {
			$report = $this->setRelationships($report, $prg);
			$results = $report->getResults(false, null, $sort, $order);
		} else {
			$results = [ ];
		}
		
		$paginator = new Paginator(new ArrayAdapter($results));
		$paginator->setCacheEnabled(true);
		$paginator->setDefaultItemCountPerPage($limit);
		$paginator->setCurrentPageNumber($page);
		$paginator->setPageRange($this->paginatorRange);
		
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
				'ui' => $ui 
		];
	
	}

	public function resultAction()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$lead = $this->getEvent()
			->getRouteMatch()
			->getParam('lead', 0);
		
		$entity = false;
		$report = false;
		
		if ($id && $lead) {
			$search = $this->getCachedParams($id);
			
			if ($search) {
				$classe = $this->getEntityClass();
				
				/* @var $report \Report\Entity\Report */
				$report = new $classe();
				$report->setServiceLocator($this->getServiceLocator());
				
				if (method_exists($this, 'getSearchForm')) {
					$form = $this->getSearchForm();
				} else {
					$form = $this->getForm();
				}
				
				$form->bind($report);
				
				$form->setData($search);
				
				if ($form->isValid()) {
					$report = $this->setRelationships($report, $search);
					$report->setId($id);
					$report->setName('Lead Search');
					$entity = $report->findResult($lead);
				}
			}
		}
		
		return [ 
				'entity' => $entity,
				'report' => $report 
		];
	
	}

	public function exportAction()
	{
		$results = array ();
		$labels = array ();
		$headings = array ();
		$this->exportHeadings = $headings;
		
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$limit = $this->getLimit($this->defaultPageSize);
		
		$sort = $this->getRequest()
			->getQuery('sort', $this->defaultSort);
		$order = $this->getRequest()
			->getQuery('order', $this->defaultOrder);
		
		if (empty($sort)) {
			$sort = $this->defaultSort;
		}
		
		$classe = $this->getEntityClass();
		
		/* @var $report \Report\Entity\Report */
		$report = new $classe();
		$report->setServiceLocator($this->getServiceLocator());
		
		if (method_exists($this, 'getSearchForm')) {
			$form = $this->getSearchForm();
		} else {
			$form = $this->getForm();
		}
		
		$form->bind($report);
		
		if ($id) {
			$form->get('id')
				->setValue($id);
			// Retrieve Session-Cached data
			$cachedData = $this->getCachedParams($id);
			if ($cachedData) {
				$form->setData($cachedData);
				if ($form->isValid()) {
					$form->get('id')
						->setValue($id);
					
					if ($report) {
						$report = $this->setRelationships($report, $cachedData);
						$results = $report->getResults(false, null, $sort, $order);
						
						if ($results) {
							
							$em = $this->getEntityManager();
							$attributeRepository = $em->getRepository("Lead\\Entity\\LeadAttribute");
							
							$attributes = $attributeRepository->getUniqueArray();
							
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
						
						}
					}
				}
			}
		}
		
		return $this->csvExport('Search Results (' . date('Y-m-d') . ').csv', $this->exportHeadings, $results, array (
				$this,
				'extractResult' 
		));
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::getForm()
	 *
	 * @return \Zend\Form\Form
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
					->setLabel('Search');
			}
			if ($form->has('cancelar')) {
				$form->get('cancelar')
					->setLabel('Cancel')
					->setName('cancel');
			}
		}
		return $form;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::getAddForm()
	 *
	 * @return \Lead\Form\SearchForm
	 */
	protected function getSearchForm($data = array())
	{
		$dateTime = date('Y-m-d H:i:s');
		$sl = $this->getServiceLocator();
		/* @var $form \Lead\Form\SearchForm */
		$form = $sl->get('FormElementManager')
			->get('Lead\Form\SearchFormFactory');
		$form->get('updated')
			->setValue($dateTime);
		$form->get('name')
			->setValue('Search ' . $dateTime);
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

	public function getRouteName()
	{
		return 'lead';
	}

	public function getModuleName()
	{
		return 'Report';
	}

	public function getEntityClass()
	{
		$module = $this->getModuleName();
		
		return "{$module}\\Entity\\{$module}";
	}

	public function getEntityServiceClass()
	{
		$module = $this->getModuleName();
		
		return "$module\\Service\\{$module}";
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

	protected function setCachedParams($id, $data)
	{
		$cache = $this->getSessionCache('cachedReport');
		$_data = $cache->_data;
		if (!$_data) {
			$_data = [ ];
		}
		$_data [$id] = $data;
		$cache->_data = $_data;
	}

	protected function getCachedParams($id)
	{
		$cache = $this->getSessionCache('cachedReport');
		if ($cache && $cache->_data) {
			$_data = $cache->_data;
			
			return isset($_data [$id]) ? $_data [$id] : false;
		}
		return false;
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