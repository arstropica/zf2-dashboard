<?php

namespace Lead\Controller;

use Application\Controller\AbstractCrudController;
use Zend\Paginator\Paginator;
use User\Provider\IdentityAwareTrait;
use Doctrine\ORM\QueryBuilder as Builder;
use Zend\Paginator\Adapter\ArrayAdapter;
use Application\Utility\Helper;
use Zend\Stdlib\ResponseInterface as Response;
use Lead\Form\Source\EditForm;
use Lead\Entity\Lead;
use Lead\Form\Source\MergeForm;

/**
 *
 * @author arstropica
 *        
 */
class SourceController extends AbstractCrudController {
	
	use IdentityAwareTrait;
	
	protected $batchSize = 20;
	
	protected $defaultSort = 'referrer';
	
	protected $defaultOrder = 'asc';
	
	protected $defaultPageSize = 10;
	
	protected $paginatorRange = 5;
	
	protected $successEditMessage = 'The Lead Source(s) were successfully edited.';
	
	protected $successMergeMessage = 'The Lead Source(s) were successfully merged.';
	
	protected $errorEditMessage = 'There was a problem editing your Lead Source(s).';
	
	protected $errorMergeMessage = 'There was a problem merging your Lead Source(s).';

	public function listAction()
	{
		$pagerAction = $this->handlePager();
		$limit = $this->getLimit($this->defaultPageSize);
		// $limit = 100;
		
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
		$qb->select([ 
				'e.referrer',
				'COUNT(e.id) AS refcount' 
		])
			->from($this->getEntityClass(), 'e')
			->setFirstResult($offset)
			->setMaxResults($limit)
			->groupBy('e.referrer')
			->orderBy('e.' . $sort, $order);
		
		$qb = $this->handleSearch($qb);
		
		$pager = $this->getPagerForm($limit);
		
		$q = $qb->getQuery();
		
		$q->setMaxResults($limit);
		$q->setFirstResult($offset);
		
		$results = $this->getSources($q->getArrayResult());
		
		$paginator = new Paginator(new ArrayAdapter($results));
		$paginator->setCacheEnabled(true);
		$paginator->setDefaultItemCountPerPage($limit);
		$paginator->setCurrentPageNumber($page);
		$paginator->setPageRange($this->paginatorRange);
		
		$ui = [ 
				'table' => [ 
						"source" => [ 
								"col" => 3,
								"label" => "Source",
								"sort" => false 
						],
						"referrers" => [ 
								"col" => 6,
								"label" => "Referrers",
								"sort" => false 
						],
						"count" => [ 
								"col" => 1,
								"label" => "# Leads",
								"sort" => false 
						] 
				] 
		];
		
		return [ 
				'paginator' => $paginator,
				'sort' => $sort,
				'order' => $order,
				'page' => $page,
				'pager' => $pager,
				'query' => $this->params()
					->fromQuery(),
				'ui' => $ui,
				'isAdmin' => $this->isAdmin(),
				'history' => $this->setHistory() 
		];
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::editAction()
	 */
	public function editAction()
	{
		$source = $this->params()
			->fromQuery('source', false);
		
		if ($source) {
			if (method_exists($this, 'getEditForm')) {
				$form = $this->getEditForm();
			} else {
				$form = $this->getForm();
			}
			
			$form->get('source')
				->setValue($source);
			
			$redirectUrl = $this->url()
				->fromRoute($this->getActionRoute(), [ ], [ 
					'query' => $this->params()
						->fromQuery() 
			], true);
			$prg = $this->fileprg($form, $redirectUrl, true);
			
			if ($prg instanceof Response) {
				return $prg;
			} elseif ($prg === false) {
				return [ 
						'entityForm' => $form,
						'source' => $source,
						'history' => $this->setHistory() 
				];
			}
			
			$success = false;
			$sources = $this->getSources();
			
			if (isset($prg ['source'], $sources [$source]) && $prg ['source']) {
				$referrers = $sources [$source] ['referrers'];
				if ($referrers) {
					$referrer = Helper::add_protocol(strtolower($prg ['source']));
					$success = $this->setReferrerLeads($referrer, $referrers);
				}
			}
			if (!$success) {
				$this->flashMessenger()
					->addSuccessMessage($this->getServiceLocator()
					->get('translator')
					->translate($this->errorEditMessage));
				return [ 
						'entityForm' => $form,
						'source' => $source,
						'history' => $this->getHistory() 
				];
			}
		}
		return $this->getHistoricalRedirect();
	}

	public function mergeAction()
	{
		$source = $this->params()
			->fromQuery('source', false);
		
		if ($source) {
			$form = $this->getMergeForm();
			
			$form->get('source')
				->setValue($source);
			
			$redirectUrl = $this->url()
				->fromRoute($this->getActionRoute(), [ ], [ 
					'query' => $this->params()
						->fromQuery() 
			], true);
			$prg = $this->fileprg($form, $redirectUrl, true);
			
			if ($prg instanceof Response) {
				return $prg;
			} elseif ($prg === false) {
				$message = "Note: Merging sources will modify referrers for all leads matching: \"" . $source . "\".";
				$this->flashMessenger()
					->addInfoMessage($message);
				
				return [ 
						'entityForm' => $form,
						'source' => $source,
						'history' => $this->setHistory() 
				];
			}
			
			$success = false;
			$source = isset($prg ['source']) ? $prg ['source'] : false;
			$merge = isset($prg ['merge']) ? $prg ['merge'] : false;
			$sources = $this->getSources();
			
			if ($source && $merge && isset($sources [$source])) {
				$referrers = $sources [$source] ['referrers'];
				if ($referrers) {
					$referrer = Helper::add_protocol($merge);
					$success = $this->setReferrerLeads($referrer, $referrers);
				}
			}
			if (!$success) {
				$this->flashMessenger()
					->addSuccessMessage($this->getServiceLocator()
					->get('translator')
					->translate($this->errorMergeMessage));
				return [ 
						'entityForm' => $form,
						'source' => $source,
						'history' => $this->getHistory() 
				];
			}
		}
		return $this->getHistoricalRedirect();
	}

	public function getEditForm()
	{
		return new EditForm();
	}

	public function getMergeForm()
	{
		return new MergeForm($this->getSources([ ], true));
	}

	public function handleSearch(Builder $qb)
	{
		$query = $this->getRequest()
			->getQuery();
		
		$filters = [ 
				'referrer' 
		];
		if ($query) {
			$where = [ ];
			$params = [ ];
			foreach ( $filters as $condition ) {
				if (isset($query [$condition]) && "" !== $query [$condition]) {
					switch ($condition) {
						case 'referrer' :
							$where ['referrer'] = "%{$query[$condition]}%";
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
		$qb->andWhere('e.active = 1');
		return $qb;
	}

	protected function getReferrerLeads($referrers)
	{
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()
			->createQueryBuilder();
		$qb->add('select', 'e')
			->add('from', $this->getEntityClass() . ' e')
			->where('e.referrer IN (:referrers)')
			->andWhere('e.active = 1')
			->setParameter('referrers', $referrers);
		return $qb->getQuery()
			->getResult();
	}

	protected function setReferrerLeads($referrer, $referrers = [], $replace = true)
	{
		$res = false;
		if ($referrer && $referrers) {
			$leads = $this->getReferrerLeads($referrers);
			if ($leads) {
				$res = true;
				$count = 0;
				$total = 0;
				$em = $this->getEntityManager();
				$i = 1;
				foreach ( $leads as $lead ) {
					try {
						$referrer_new = $referrer;
						if ($replace) {
							$domain_to = parse_url(Helper::add_protocol($referrer), PHP_URL_HOST);
							$domain_from = parse_url(Helper::add_protocol($lead->getReferrer()), PHP_URL_HOST);
							if ($domain_to && $domain_from) {
								$referrer_new = preg_replace('/' . preg_quote($domain_from) . '/i', $domain_to, $lead->getReferrer());
							}
						}
						$res = $this->setReferrerLead($referrer_new, $lead) ? $res : false;
						$count = $res ? $count + 1 : $count;
						$total++;
						if (($i % $this->batchSize) == 0) {
							$em->flush();
							$em->clear();
						}
						$i++;
					} catch ( \Exception $e ) {
						$res = false;
					}
				}
				$em->flush();
				$em->clear();
				if ($res) {
					$message = str_replace("The", "{$count} out of {$total}", $this->successEditMessage);
					$this->flashMessenger()
						->addSuccessMessage($this->getServiceLocator()
						->get('translator')
						->translate($message));
				} else {
					$message_part = " " . ($total - $count) . " of {$total} Lead(s) were not successfully ";
					$message = $this->errorEditMessage . $message_part . "edited.";
					$this->flashMessenger()
						->addErrorMessage($this->getServiceLocator()
						->get('translator')
						->translate($message));
				}
			}
		}
		return $res;
	}

	protected function setReferrerLead($referrer, Lead $lead, $flush = false)
	{
		$em = $this->getEntityManager();
		$this->createServiceEvent()
			->setEntityId($lead->getId())
			->setEntityClass($this->getEntityClass())
			->setDescription("Lead Edited");
		try {
			$lead->setReferrer($referrer);
			$em->merge($lead);
			if ($flush) {
				$em->flush();
				$em->detach($lead);
			}
			$this->getServiceEvent()
				->setMessage("Referrer for Lead #{$lead->getId()} was edited.");
			$this->logEvent("EditAction.post");
		} catch ( \Exception $e ) {
			$this->logError($e);
			return false;
		}
		return true;
	}

	protected function getReferrers()
	{
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()
			->createQueryBuilder();
		$qb->select([ 
				'e.referrer',
				'COUNT(e.id) AS refcount' 
		])
			->from($this->getEntityClass(), 'e')
			->groupBy('e.referrer');
		
		return $qb->getQuery()
			->getArrayResult();
	}

	protected function getSources(Array $results = [], $list = false)
	{
		$sources = [ ];
		if (!$results) {
			$results = $this->getReferrers();
		}
		foreach ( $results as $record ) {
			$referrer = Helper::add_protocol(strtolower($record ['referrer']));
			$domain = $referrer ? parse_url($referrer, PHP_URL_HOST) : false;
			$count = $record ['refcount'];
			if (!$domain) {
				$domain = 'unknown';
			}
			if (isset($sources [$domain])) {
				$sources [$domain] ['referrers'] [] = $record ['referrer'];
				$sources [$domain] ['count'] += $count;
			} else {
				$sources [$domain] = [ 
						'source' => $domain,
						'count' => $count,
						'referrers' => [ 
								$record ['referrer'] 
						] 
				];
			}
		}
		if ($list) {
			$sources = array_combine(array_keys($sources), array_keys($sources));
		}
		return $sources;
	}

	public function getRouteName()
	{
		return 'source';
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