<?php

namespace Lead\Controller;

use Application\Controller\AbstractCrudController;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrinePaginatorAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
// use LosBase\ORM\Tools\Pagination\Paginator as LosPaginator;
use Zend\Stdlib\ResponseInterface as Response;
use Lead\Entity\LeadAttribute;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Lead\Form\Attribute\MergeForm;
use User\Provider\IdentityAwareTrait;
use Doctrine\ORM\QueryBuilder as Builder;
use Zend\View\Model\JsonModel;
use Lead\Model\LeadAttributeModel;
use Doctrine\Common\Collections\ArrayCollection;

/**
 *
 * @author arstropica
 *        
 */
class AttributeController extends AbstractCrudController {
	
	use IdentityAwareTrait;

	protected $defaultSort = 'attributeOrder';

	protected $defaultOrder = 'asc';

	protected $defaultPageSize = 10;

	protected $paginatorRange = 5;

	protected $uniqueField = null;

	protected $uniqueEntityMessage = null;

	protected $successAddMessage = 'The Lead Attribute(s) were successfully added.';

	protected $successEditMessage = 'The Lead Attribute(s) were successfully edited.';

	protected $successAssignMessage = 'The Lead Attribute(s) were successfully assigned.';

	protected $successSubmitMessage = 'The Lead Attribute(s) were successfully submitted.';

	protected $successDeleteMessage = 'The Lead Attribute(s) were successfully deleted.';

	protected $successMergeMessage = 'The Lead Attribute(s) were successfully merged.';

	protected $errorEditMessage = 'There was a problem assigning your Lead Attribute(s).';

	protected $errorAssignMessage = 'There was a problem assigning your Lead Attribute(s).';

	protected $errorSubmitMessage = 'There was a problem submitting your Lead Attribute(s).';

	protected $errorDeleteMessage = 'There was a problem deleting your Lead Attribute(s).';

	protected $errorMergeMessage = 'There was a problem merging your Lead Attribute(s).';

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
			->setFirstResult($offset)
			->setMaxResults($limit);
		
		$sortable = false;
		if ($sort == 'count') {
			$qb->groupBy('e');
			$qb->addSelect('COUNT(v.id) AS HIDDEN vcount');
			$qb->leftJoin('e.values', 'v');
			$qb->orderBy('vcount', $order);
		} elseif ($sort == 'attributeOrder') {
			$sortable = true;
			$qb->addSelect('-e.attributeOrder AS HIDDEN vcount');
			$qb->orderBy('vcount', $this->reverseOrder($order));
		} else {
			$qb->orderBy('e.' . $sort, $order);
		}
		
		$qb = $this->handleSearch($qb);
		
		$pager = $this->getPagerForm($limit);
		
		$q = $qb->getQuery();
		
		$q->setMaxResults($limit);
		$q->setFirstResult($offset);
		
		$paginator = new Paginator(new DoctrinePaginatorAdapter(new DoctrinePaginator($q, $fetchJoin = true)));
		$paginator->setDefaultItemCountPerPage($limit);
		$paginator->setCurrentPageNumber($page);
		$paginator->setPageRange($this->paginatorRange);
		
		$ui = [ 
				'table' => [ 
						"attributeOrder" => [ 
								"col" => 1,
								"label" => "Order",
								"sort" => true 
						],
						/* "attributeName" => [
								"col" => 2,
								"label" => "Field ID",
								"sort" => true
						], */
						"attributeDesc" => [ 
								"col" => $sortable ? 7 : 8,
								"label" => "Attribute",
								"sort" => true 
						],
						"count" => [ 
								"col" => 1,
								"label" => "# Leads",
								"sort" => true 
						] 
				] 
		];
		
		$filters = $this->getFilterForm($this->params()
			->fromQuery());
		
		return [ 
				'paginator' => $paginator,
				'filters' => $filters,
				'sort' => $sort,
				'order' => $order,
				'page' => $page,
				'pager' => $pager,
				'query' => $this->params()
					->fromQuery(),
				'ui' => $ui,
				'isAdmin' => $this->isAdmin(),
				'sortable' => $sortable,
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
		
		$classe = $this->getEntityClass();
		$entity = new $classe();
		
		$entity->setAttributeName('Question');
		
		$this->getEventManager()
			->trigger('getForm', $this, [ 
				'form' => $form,
				'entityClass' => $this->getEntityClass(),
				'id' => 0,
				'entity' => $entity 
		]);
		
		$form->bind($entity);
		
		$redirectUrl = $this->url()
			->fromRoute($this->getActionRoute(), [ ], [ 
				'query' => $this->params()
					->fromQuery() 
		], true);
		$prg = $this->fileprg($form, $redirectUrl, true);
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false) {
			$this->getEventManager()
				->trigger('getForm', $this, [ 
					'form' => $form,
					'entityClass' => $this->getEntityClass(),
					'entity' => $entity 
			]);
			
			return [ 
					'entityForm' => $form,
					'entity' => $entity,
					'history' => $this->setHistory() 
			];
		}
		
		$this->createServiceEvent()
			->setEntityClass($this->getEntityClass())
			->setDescription("Lead Attribute Created");
		
		$this->getEventManager()
			->trigger('add', $this, [ 
				'form' => $form,
				'entityClass' => $this->getEntityClass(),
				'entity' => $entity 
		]);
		
		$attribute_count = $this->getEntityManager()
			->getRepository($this->getEntityClass())
			->getCount();
		
		$entity->setAttributeOrder($attribute_count);
		
		$savedEntity = $this->getEntityService()
			->save($form, $entity);
		
		if ($savedEntity && $savedEntity instanceof LeadAttribute) {
			$id = $savedEntity->getId();
			$desc = $savedEntity->getAttributeDesc();
			$this->getServiceEvent()
				->setEntityId($id)
				->setMessage("Lead Attribute '{$desc}' was created.");
			
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
		
		$this->getEventManager()
			->trigger('getForm', $this, [ 
				'form' => $form,
				'entityClass' => $this->getEntityClass(),
				'id' => $id,
				'entity' => $entity 
		]);
		
		$form->bind($entity);
		
		if ($entity && $entity->getAttributeName() != 'Question') {
			$form->get('attributeDesc')
				->setAttribute('readonly', 'readonly');
		}
		
		$redirectUrl = $this->url()
			->fromRoute($this->getActionRoute(), [ ], [ 
				'query' => $this->params()
					->fromQuery() 
		], true);
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
			->setDescription("Lead Attribute Edited");
		
		$this->getEventManager()
			->trigger('edit', $this, [ 
				'form' => $form,
				'entityClass' => $this->getEntityClass(),
				'id' => $id,
				'entity' => $entity 
		]);
		
		$savedEntity = $this->getEntityService()
			->save($form, $entity);
		
		if ($savedEntity && $savedEntity instanceof LeadAttribute) {
			$desc = $savedEntity->getAttributeDesc();
			$this->getServiceEvent()
				->setMessage("Lead Attribute '{$desc}' was edited.");
			
			$this->logEvent("EditAction.post");
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
		
		return $this->getHistoricalRedirect();
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
			->fromRoute($this->getActionRoute(), [ ], [ 
				'query' => $this->params()
					->fromQuery() 
		], true);
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
		
		if ($this->validateDelete($post)) {
			if ($this->getEntityService()
				->archive($entity)) {
				$this->flashMessenger()
					->addSuccessMessage($this->getServiceLocator()
					->get('translator')
					->translate($this->successDeleteMessage));
				
				return $this->getHistoricalRedirect('list', true);
			}
		}
		
		$this->flashMessenger()
			->addErrorMessage($this->getServiceLocator()
			->get('translator')
			->translate($this->errorDeleteMessage));
		
		return [ 
				'entity' => $entity,
				'history' => $this->getHistory() 
		];
	}

	public function mergeAction()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$form = $this->getMergeForm();
		
		$form->get('attribute')
			->setValue($id);
		
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
		
		$redirectUrl = $this->url()
			->fromRoute($this->getActionRoute(), [ ], [ 
				'query' => $this->params()
					->fromQuery() 
		], true);
		$prg = $this->fileprg($form, $redirectUrl, true);
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false) {
			$message = "Note: an attribute that is merged with another is deleted afterwards.";
			$this->flashMessenger()
				->addInfoMessage($message);
			
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
		
		$merge_success = false;
		$delete_success = false;
		
		$merge_attribute_id = $prg ['merge'];
		
		if ($merge_attribute_id) {
			
			$entity_description = $entity->getAttributeDesc();
			$merged_attribute = $objRepository->find($merge_attribute_id);
			
			if ($merged_attribute) {
				$merged_description = $merged_attribute->getAttributeDesc();
				
				$attribute_values = new ArrayCollection($entity->getValues(false));
				
				try {
					if ($attribute_values->count() > 0) {
						$entity->removeValues($attribute_values);
						$em->merge($entity);
						$merged_attribute->addValues($attribute_values);
						$em->persist($merged_attribute);
						$em->flush();
						$merge_success = true;
					}
					
					if ($this->getEntityService()
						->archive($entity)) {
						$em->flush();
						$delete_success = true;
					}
				} catch ( \Exception $e ) {
					$this->createServiceEvent()
						->setEntityId($merge_attribute_id)
						->setEntityClass($this->getEntityClass())
						->setDescription("Lead Attribute Merged");
					$this->logError($e);
				}
			}
		}
		
		if ($merge_success) {
			$this->createServiceEvent()
				->setEntityId($merge_attribute_id)
				->setEntityClass($this->getEntityClass())
				->setDescription("Lead Attribute Merged")
				->setMessage("Lead Attribute: {$entity_description} was merged with {$merged_description}.");
			$this->logEvent("EditAction.post");
			$this->flashMessenger()
				->addSuccessMessage($this->getServiceLocator()
				->get('translator')
				->translate($this->successMergeMessage));
		} else {
			$this->flashMessenger()
				->addErrorMessage($this->getServiceLocator()
				->get('translator')
				->translate($this->errorMergeMessage));
		}
		
		if ($delete_success) {
			$this->createServiceEvent()
				->setEntityId($id)
				->setEntityClass($this->getEntityClass())
				->setDescription("Lead Attribute Deleted")
				->setMessage("Lead Attribute: {$entity_description} was deleted.");
			$this->logEvent("DeleteAction.post");
			$this->flashMessenger()
				->addSuccessMessage($this->getServiceLocator()
				->get('translator')
				->translate($this->successDeleteMessage));
		} else {
			$this->flashMessenger()
				->addErrorMessage($this->getServiceLocator()
				->get('translator')
				->translate($this->errorDeleteMessage));
		}
		
		$this->getEventManager()
			->trigger('edit', $this, [ 
				'form' => $form,
				'entityClass' => $this->getEntityClass(),
				'id' => $id,
				'entity' => $entity 
		]);
		
		if (!$merge_success || !$delete_success) {
			return [ 
					'entityForm' => $form,
					'entity' => $entity,
					'history' => $this->getHistory() 
			];
		}
		
		return $this->getHistoricalRedirect();
	}

	public function sortAction()
	{
		$result = false;
		$key = 'attributeOrder';
		$updated = 0;
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$index = $this->params()
			->fromPost('index', null);
		$order = $this->params()
			->fromPost('order', 'asc');
		$global = $this->params()
			->fromPost('domUpdate', false);
		
		if (isset($index)) {
			$model = $this->getModel();
			
			$resultset = $model->fetch($id);
			if ($resultset) {
				$entity = current($resultset);
				$entity [$key] = $index;
				$update = $model->update($id, $entity);
				
				if ($update) {
					$result = 1;
					if ($global) {
						$sorted = $model->fetchByOrder($key, $order, true, $id, 'e.active = 1');
						$updated = $model->bulkUpdate($sorted);
						if (!$updated) {
							$result = false;
						}
					}
				}
			}
		}
		
		return new JsonModel([ 
				'result' => $result,
				'collection' => $model->fetchByOrder($key, $order, false, null, 'e.active = 1'),
				'updated' => $updated 
		]);
	}
	
	public function geoAction(){
		return array();
	}

	/**
	 * Get LeadAttribute Model
	 *
	 * @return LeadAttributeModel
	 */
	protected function getModel()
	{
		return $this->getServiceLocator()
			->get('Lead\Model\LeadAttribute');
	}

	public function getForm($entityClass = null)
	{
		$form = parent::getForm($entityClass);
		
		if ($form) {
			$entityClass = $entityClass ?: $this->getEntityClass();
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

	public function getAddForm($data = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('Lead\Form\Attribute\AddFormFactory');
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

	/**
	 * Get Attribute Merge Form
	 *
	 * @param array $data        	
	 * @return MergeForm
	 */
	public function getMergeForm($data = array())
	{
		$sl = $this->getServiceLocator();
		/* @var $form MergeForm */
		$form = $sl->get('Lead\Form\Attribute\MergeFormFactory');
		$form->setAttribute('action', $this->url()
			->fromRoute($this->getActionRoute('merge'), [ ], [ 
				'query' => $this->params()
					->fromQuery() 
		], true));
		$form->get('cancel')
			->setAttributes([ 
				'onclick' => 'top.location=\'' . $this->url()
					->fromRoute($this->getActionRoute('list'), [ ], [ 
						'query' => $this->params()
							->fromQuery() 
				], true) . '\'' 
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

	public function getFilterForm($data = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('FormElementManager')
			->get('Lead\Form\Attribute\FilterForm');
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
				'attributeDesc' 
		];
		if ($query) {
			$where = [ ];
			$params = [ ];
			foreach ( $filters as $condition ) {
				if (isset($query [$condition]) && "" !== $query [$condition]) {
					switch ($condition) {
						case 'attributeDesc' :
							$where ['attributeDesc'] = "%{$query[$condition]}%";
							$qb->andWhere("e.attributeDesc LIKE :attributeDesc");
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

	protected function reverseOrder($order)
	{
		return strtolower($order) == 'asc' ? 'desc' : 'asc';
	}

	public function getRouteName()
	{
		return 'attribute';
	}

	public function getEntityClass()
	{
		$module = $this->getModuleName();
		
		return "{$module}\\Entity\\{$module}Attribute";
	}

	public function getEntityServiceClass()
	{
		$module = $this->getModuleName();
		
		return "$module\\Service\\{$module}Attribute";
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