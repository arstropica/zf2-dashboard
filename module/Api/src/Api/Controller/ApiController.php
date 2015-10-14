<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Api for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Api\Controller;
use Application\Controller\AbstractCrudController;
use Doctrine\ORM\QueryBuilder as Builder;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Zend\Stdlib\ResponseInterface as Response;
use Api\Entity\Api;

class ApiController extends AbstractCrudController
{

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
		
		foreach ($form->get('options') as $apiOption) {
			$scope = $apiOption->get('scope')->getValue();
			$apiOption->setAttribute('class', $scope);
			switch ($scope) {
				case 'local':
					$apiOption->get('value')->setAttribute('readonly', 
							'readonly');
					break;
			}
		}
		
		$redirectUrl = $this->url()->fromRoute($this->getMatchedRoute(), [], 
				true);
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
			->setDescription("Api Options Edited");
		
		$this->getEventManager()->trigger('edit', $this, 
				[
						'form' => $form,
						'entityClass' => $this->getEntityClass(),
						'id' => $id,
						'entity' => $entity
				]);
		
		if (! $form->isValid()) {
			return [
					'entityForm' => $form,
					'entity' => $entity
			];
		}
		
		$savedEntity = $this->getEntityService()->save($form, $entity);
		
		if ($savedEntity && $savedEntity instanceof Api) {
			$name = $savedEntity->getName();
			$this->getServiceEvent()->setMessage(
					"Global options for {$name} were edited.");
			
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
		
		return $this->redirect()->toRoute('home');
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

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::getEditForm()
	 * @return \Account\Form\EditForm
	 */
	protected function getEditForm ($data = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('Api\Form\EditFormFactory');
		$form->get('cancel')->setAttribute('onclick', 
				'top.location=\'' . $this->url()
					->fromRoute('home', [
						'action' => 'index'
				]) . '\'');
		$form->setInputFilter($form->getInputFilter());
		if ($data) {
			$form->setData($data);
			if (! $form->isValid()) {
				$form->setData(array());
			}
		}
		return $form;
	}

	public function handleSearch (Builder $qb)
	{
		$query = $this->getRequest()->getQuery();
		$filters = [
				'api',
				'apiOption'
		];
		if ($query) {
			$where = [];
			$params = [];
			foreach ($filters as $condition) {
				if (isset($query[$condition]) && "" !== $query[$condition]) {
					switch ($condition) {
						case 'api':
							$where['api'] = $query[$condition];
							$qb->innerJoin('e.api', 'p')->andWhere(
									"p.id = :api");
							break;
						case 'apiOption':
							$where['apiOption'] = $query[$condition];
							$qb->andWhere("e.apiOption = :apiOption");
							break;
					}
				}
			}
			if ($where) {
				foreach ($where as $key => $value) {
					$qb->setParameter($key, $value);
				}
			}
		}
		return $qb;
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

