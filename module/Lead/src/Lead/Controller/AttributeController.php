<?php
namespace Lead\Controller;
use Application\Controller\AbstractCrudController;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use LosBase\ORM\Tools\Pagination\Paginator as LosPaginator;
use Zend\Stdlib\ResponseInterface as Response;
use Lead\Entity\LeadAttribute;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;

/**
 *
 * @author arstropica
 *        
 */
class AttributeController extends AbstractCrudController
{

	protected $defaultSort = 'id';

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

	protected $errorEditMessage = 'There was a problem assigning your Lead Attribute(s).';

	protected $errorAssignMessage = 'There was a problem assigning your Lead Attribute(s).';

	protected $errorSubmitMessage = 'There was a problem submitting your Lead Attribute(s).';

	protected $errorDeleteMessage = 'There was a problem deleting your Lead Attribute(s).';

	public function listAction ()
	{
		$pagerAction = $this->handlePager();
		$limit = $this->getLimit($this->defaultPageSize);
		
		$page = $this->getRequest()->getQuery('page', 0);
		$sort = $this->getRequest()->getQuery('sort', $this->defaultSort);
		$order = $this->getRequest()->getQuery('order', $this->defaultOrder);
		
		if (empty($sort)) {
			$sort = $this->defaultSort;
		}
		
		$offset = $limit * $page - $limit;
		if ($offset < 0) {
			$offset = 0;
		}
		
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->add('select', 'e')
			->add('from', $this->getEntityClass() . ' e')
			->orderBy('e.' . $sort, $order)
			->setFirstResult($offset)
			->setMaxResults($limit);
		
		$pager = $this->getPagerForm($limit);
		
		$paginator = new Paginator(
				new DoctrinePaginator(new LosPaginator($qb, false)));
		$paginator->setDefaultItemCountPerPage($limit);
		$paginator->setCurrentPageNumber($page);
		$paginator->setPageRange($this->paginatorRange);
		
		$ui = [
				'table' => [
						"name" => [
								"col" => 2,
								"label" => "Name",
								"sort" => true
						],
						"description" => [
								"col" => 9,
								"label" => "Description",
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
				'query' => $this->params()->fromQuery(),
				'ui' => $ui
		];
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::addAction()
	 */
	public function addAction ()
	{
		if (method_exists($this, 'getAddForm')) {
			$form = $this->getAddForm();
		} else {
			$form = $this->getForm();
		}
		
		$classe = $this->getEntityClass();
		$entity = new $classe();
		
		$entity->setAttributeName('Question');
		
		$this->getEventManager()->trigger('getForm', $this, 
				[
						'form' => $form,
						'entityClass' => $this->getEntityClass(),
						'id' => 0,
						'entity' => $entity
				]);
		
		$form->bind($entity);
		
		$redirectUrl = $this->url()->fromRoute($this->getActionRoute(), [], true);
		$prg = $this->fileprg($form, $redirectUrl, true);
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false) {
			$this->getEventManager()->trigger('getForm', $this, 
					[
							'form' => $form,
							'entityClass' => $this->getEntityClass(),
							'entity' => $entity
					]);
			
			return [
					'entityForm' => $form,
					'entity' => $entity
			];
		}
		
		$this->createServiceEvent()
			->setEntityClass($this->getEntityClass())
			->setDescription("Lead Attribute Created");
		
		$this->getEventManager()->trigger('add', $this, 
				[
						'form' => $form,
						'entityClass' => $this->getEntityClass(),
						'entity' => $entity
				]);
		
		$savedEntity = $this->getEntityService()->save($form, $entity);
		
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
					'entity' => $entity
			];
		}
		
		$this->flashMessenger()->addSuccessMessage(
				$this->getServiceLocator()
					->get('translator')
					->translate($this->successAddMessage));
		
		if ($this->needAddOther($form)) {
			$action = 'add';
		} else {
			$action = 'list';
		}
		
		return $this->redirect()->toRoute($this->getActionRoute($action), [], 
				true);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::editAction()
	 */
	public function editAction ()
	{
		if (method_exists($this, 'getEditForm')) {
			$form = $this->getEditForm();
		} else {
			$form = $this->getForm();
		}
		
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		$form->add(
				[
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
		
		$this->getEventManager()->trigger('getForm', $this, 
				[
						'form' => $form,
						'entityClass' => $this->getEntityClass(),
						'id' => $id,
						'entity' => $entity
				]);
		
		$form->bind($entity);
		
		$redirectUrl = $this->url()->fromRoute($this->getActionRoute(), [], true);
		$prg = $this->fileprg($form, $redirectUrl, true);
		
		if ($prg instanceof Response) {
			return $prg;
		} elseif ($prg === false) {
			$this->getEventManager()->trigger('getForm', $this, 
					[
							'form' => $form,
							'entityClass' => $this->getEntityClass(),
							'id' => $id,
							'entity' => $entity
					]);
			
			return [
					'entityForm' => $form,
					'entity' => $entity
			];
		}
		
		$this->createServiceEvent()
			->setEntityId($id)
			->setEntityClass($this->getEntityClass())
			->setDescription("Lead Attribute Edited");
		
		$this->getEventManager()->trigger('edit', $this, 
				[
						'form' => $form,
						'entityClass' => $this->getEntityClass(),
						'id' => $id,
						'entity' => $entity
				]);
		
		$savedEntity = $this->getEntityService()->save($form, $entity);
		
		if ($savedEntity && $savedEntity instanceof LeadAttribute) {
			$desc = $savedEntity->getAttributeDesc();
			$this->getServiceEvent()->setMessage(
					"Lead Attribute '{$desc}' was edited.");
			
			$this->logEvent("EditAction.post");
		} else {
			return [
					'entityForm' => $form,
					'entity' => $entity
			];
		}
		
		$this->flashMessenger()->addSuccessMessage(
				$this->getServiceLocator()
					->get('translator')
					->translate($this->successEditMessage));
		
		return $this->redirect()->toRoute($this->getActionRoute('list'), [], 
				true);
	}

	public function getForm ($entityClass = null)
	{
		$form = parent::getForm($entityClass);
		
		if ($form) {
			$entityClass = $entityClass ?  : $this->getEntityClass();
			$hydrator = new DoctrineHydrator($this->getEntityManager(), 
					$entityClass);
			$form->setHydrator($hydrator);
			if ($form->has('submit')) {
				$form->get('submit')->setLabel('Save');
			}
			if ($form->has('cancelar')) {
				$form->get('cancelar')
					->setLabel('Cancel')
					->setName('cancel');
			}
		}
		return $form;
	}

	protected function getAddForm ($data = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('Lead\Form\Attribute\AddFormFactory');
		$form->get('cancel')->setAttributes(
				[
						'onclick' => 'top.location=\'' . $this->url()
							->fromRoute($this->getActionRoute('list')) . '\''
				]);
		$form->setInputFilter($form->getInputFilter());
		if ($data) {
			$form->setData($data);
			if (! $form->isValid()) {
				$form->setData(array());
			}
		}
		
		return $form;
	}

	public function getRouteName ()
	{
		return 'attribute';
	}

	public function getEntityClass ()
	{
		$module = $this->getModuleName();
		
		return "{$module}\\Entity\\{$module}Attribute";
	}

	public function getEntityServiceClass ()
	{
		$module = $this->getModuleName();
		
		return "$module\\Service\\{$module}Attribute";
	}

	protected function logEvent ($event)
	{
		$this->getEventManager()->trigger($event, $this->getServiceEvent());
	}

	protected function logError (\Exception $e, $result = [])
	{
		$this->getServiceEvent()->setIsError(true);
		$this->getServiceEvent()->setMessage($e->getMessage());
		if ($result) {
			$this->getServiceEvent()->setResult(print_r($result, true));
		} else {
			$this->getServiceEvent()->setResult($e->getTraceAsString());
		}
		$this->logEvent('RuntimeError');
	}
}

?>