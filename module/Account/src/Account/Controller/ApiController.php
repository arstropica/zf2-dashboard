<?php

namespace Account\Controller;

use Application\Controller\AbstractCrudController;
use Zend\Paginator\Paginator;
use Doctrine\ORM\QueryBuilder as Builder;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use LosBase\ORM\Tools\Pagination\Paginator as LosPaginator;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Zend\Stdlib\ResponseInterface as Response;
use Account\Entity\Account;

/**
 *
 * @author arstropica
 *        
 */
class ApiController extends AbstractCrudController {
	
	protected $defaultSort = 'id';
	
	protected $defaultOrder = 'asc';
	
	protected $defaultPageSize = 10;
	
	protected $paginatorRange = 5;
	
	protected $uniqueField = null;
	
	protected $uniqueEntityMessage = null;
	
	protected $successAddMessage = 'The API was successfully added.';
	
	protected $successEditMessage = 'The API Setting was successfully edited.';
	
	protected $successDeleteMessage = 'The API Setting was successfully deleted.';
	
	protected $errorEditMessage = 'There was a problem editing your Api Setting(s).';
	
	protected $errorDeleteMessage = 'There was a problem deleting your Api Setting.';

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
			->add('from', '\Api\Entity\ApiSetting e')
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
						"api" => [ 
								"col" => 2,
								"label" => "API",
								"sort" => true 
						],
						"apiOption" => [ 
								"col" => 5,
								"label" => "Setting",
								"sort" => true 
						],
						"apiValue" => [ 
								"col" => 3,
								"label" => "Value",
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
				'ui' => $ui,
				'history' => $this->setHistory() 
		];
	}

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
		
		$this->getEventManager()
			->trigger('getForm', $this, [ 
				'form' => $form,
				'entityClass' => $this->getEntityClass(),
				'id' => $id,
				'entity' => $entity 
		]);
		
		$form->bind($entity);
		
		$redirectUrl = $this->url()
			->fromRoute($this->getMatchedRoute(), [ ], true);
		$prg = $this->fileprg($form, $redirectUrl, true);
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false) {
			$this->getEventManager()
				->trigger('getForm', $this, [ 
					'form' => $form,
					'entityClass' => $this->getEntityClass(),
					'id' => $id,
					'entity' => $entity 
			]);
			
			return [ 
					'entityForm' => $form,
					'entity' => $entity,
					'history' => $this->setHistory() 
			];
		}
		
		$this->createServiceEvent()
			->setEntityId($id)
			->setEntityClass($this->getEntityClass())
			->setDescription("API Settings Edited");
		
		$this->getEventManager()
			->trigger('edit', $this, [ 
				'form' => $form,
				'entityClass' => $this->getEntityClass(),
				'id' => $id,
				'entity' => $entity 
		]);
		
		if (!$form->isValid()) {
			return [ 
					'entityForm' => $form,
					'entity' => $entity,
					'history' => $this->getHistory() 
			];
		}
		$savedEntity = $this->getEntityService()
			->save($form, $entity);
		
		if ($savedEntity && $savedEntity instanceof Account) {
			$name = $savedEntity->getName();
			$this->getServiceEvent()
				->setMessage("API Settings were edited for {$name}.");
			$this->logEvent("EditAction.post");
		} else {
			return [ 
					'entityForm' => $form,
					'entity' => $entity, 
					'history' => $this->setHistory()
			];
		}
		
		$this->flashMessenger()
			->addSuccessMessage($this->getServiceLocator()
			->get('translator')
			->translate($this->successEditMessage));
		
		return $this->redirect()
			->toRoute($this->getMatchedRoute(), [ 
				'id' => $id 
		], true);
	}

	public function getForm($entityClass = null)
	{
		$form = parent::getForm($entityClass);
		
		if ($form) {
			$entityClass = $entityClass ?  : $this->getEntityClass();
			$hydrator = new DoctrineHydrator($this->getEntityManager(), $entityClass);
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
			->get('Api\Form\FilterForm');
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
	 * @see \LosBase\Controller\ORM\AbstractCrudController::getEditForm()
	 * @return \Account\Form\EditForm
	 */
	protected function getEditForm($data = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('Account\Form\EditFormFactory');
		$form->get('cancel')
			->setAttribute('onclick', 'top.location=\'' . $this->url()
			->fromRoute($this->getActionRoute('list')) . '\'');
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
				'api',
				'apiOption' 
		];
		if ($query) {
			$where = [ ];
			$params = [ ];
			foreach ( $filters as $condition ) {
				if (isset($query [$condition]) && "" !== $query [$condition]) {
					switch ($condition) {
						case 'api' :
							$where ['api'] = $query [$condition];
							$qb->innerJoin('e.api', 'p')
								->andWhere("p.id = :api");
							break;
						case 'apiOption' :
							$where ['apiOption'] = $query [$condition];
							$qb->andWhere("e.apiOption = :apiOption");
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
