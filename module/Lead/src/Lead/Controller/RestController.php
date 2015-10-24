<?php
namespace Lead\Controller;
use Zend\Mvc\Controller\AbstractRestfulController;
use Lead\Entity\Lead;
use Zend\EventManager\EventManagerInterface;
use OAuth2\Server as OAuth2Server;
use OAuth2\Request as OAuth2Request;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use LosBase\Entity\EntityManagerAwareTrait;
use Lead\Entity\LeadAttribute;
use Lead\Entity\LeadAttributeValue;
use Event\Entity\Event;
use Api\Entity\Api;
use Application\Provider\ServiceEventTrait;

class RestController extends AbstractRestfulController
{
	use EntityManagerAwareTrait, ServiceEventTrait;

	protected $collectionMethods = array(
			'GET',
			'POST'
	);

	protected $resourceMethods = array(
			'GET',
			'PUT',
			'DELETE'
	);

	/**
	 *
	 * @var OAuth2Server
	 */
	protected $server;

	/**
	 *
	 * @var Lead
	 */
	protected $lead;

	public function __construct (OAuth2Server $server)
	{
		$this->server = $server;
	}

	protected function authorize ()
	{
		if (! $this->server->verifyResourceRequest(
				OAuth2Request::createFromGlobals())) {
			// Not authorized return 401 error
			return false;
		}
		return true;
	}

	public function indexAction ()
	{
		if (! $this->authorize()) {
			return $this->getJsonErrorResponse('json')->errorHandler(401, 
					"Not Authorized.");
		}
		$results = $this->getList();
		return $this->getJsonErrorResponse('json')->successOperation($results);
	}

	public function addAction ()
	{
		if (! $this->authorize()) {
			return $this->getJsonErrorResponse('json')->errorHandler(401, 
					"Not Authorized.");
		}
		
		$request = $this->getRequest();
		$result = array(
				'data' => null
		);
		if ($request->isPost()) {
			$data = $request->getPost()->toArray();
			$result = $this->create($data);
		}
		
		return $this->getJsonErrorResponse('json')->successOperation($result);
	}

	public function submitAction ()
	{
		if (! $this->authorize()) {
			return $this->getJsonErrorResponse('json')->errorHandler(401, 
					"Not Authorized.");
		}
		
		$id = (int) $this->params()->fromRoute('id', 0);
		$request = $this->getRequest();
		$result = null;
		if ($request->isPost()) {
			$data = $request->getPost();
			$logger = $this->getServiceLocator()->get('Logger');
			$logger->info(print_r($data, true));
			$lead = $this->create($data);
			if ($lead instanceof Lead) {
				$id = $lead->getId();
			}
		}
		if ($id) {
			$lead = $this->getLead($id, false);
			if ($lead instanceof Lead && ($id = $lead->getId()) == true) {
				$result = $this->submit($id);
				return $this->getJsonErrorResponse('json')->successOperation(
						$result);
			}
		}
		
		return $this->getJsonErrorResponse('json')->errorHandler(400, 
				"Operation Failed. Lead " . ($id ?  : " not ") . " created.", 
				$result);
	}

	public function editAction ()
	{
		if (! $this->authorize()) {
			return $this->getJsonErrorResponse('json')->errorHandler(401, 
					"Not Authorized.");
		}
		$request = $this->getRequest();
		$result = null;
		if ($request->isPost()) {
			$data = $request->getPost();
			$id = $request->getPost('id');
			$result = $this->update($id, $data);
		}
		
		return $this->getJsonErrorResponse('json')->successOperation($result);
	}

	public function viewAction ()
	{
		if (! $this->authorize()) {
			return $this->getJsonErrorResponse('json')->errorHandler(401, 
					"Not Authorized.");
		}
		$id = (int) $this->params()->fromRoute('id', 0);
		if (! $id) {
			return $this->redirect()->toRoute('rest-api', 
					array(
							'action' => 'index'
					), array(), true);
		}
		
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder->select('t')->from($this->getEntityClass(), 't');
		
		$result = $queryBuilder->getQuery()->getResult(
				\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
		
		return $this->getJsonErrorResponse('json')->successOperation($result);
	}

	public function testAction ()
	{
		if (! $this->authorize()) {
			return $this->getJsonErrorResponse('json')->errorHandler(401, 
					"Not Authorized.");
		}
		$result = [
				'test' => 'success'
		];
		return $this->getJsonErrorResponse('json')->successOperation($result);
	}

	public function errorAction ()
	{
		return $this->getJsonErrorResponse('json')->errorHandler(400, 
				"Unspecified Error.");
	}

	protected function allowMethods ()
	{
		if ($this->params()->fromRoute('id', false)) {
			// we have an ID, return specific item
			return $this->resourceMethods;
		}
		// no ID, return collection
		return $this->collectionMethods;
	}

	public function setEventManager (EventManagerInterface $events)
	{
		// events property defined in AbstractController
		$this->events = $events;
		parent::setEventManager($events);
		// Register the listener and callback method with a priority of 10
		$events->attach('dispatch', array(
				$this,
				'checkOptions'
		), 10);
	}

	public function checkOptions ($e)
	{
		if (in_array($e->getRequest()->getMethod(), $this->allowMethods())) {
			// Method Allowed, Nothing to Do
			return;
		} else {
			// Method Not Allowed
			return $this->getJsonErrorResponse('json')->methodNotAllowed();
		}
	}

	protected function submit ($id)
	{
		$this->createServiceEvent()
			->setEntityId($id)
			->setEntityClass($this->getEntityClass());
		$access_token = $this->getRequest()->getPost('access_token');
		$response = false;
		try {
			$response = $this->forward()->dispatch('Lead\Controller\Services', 
					array(
							'action' => 'process',
							'id' => $id
					), 
					array(
							"query" => array(
									"access_token" => $access_token
							)
					), true);
		} catch (\Exception $e) {
			$this->logError($e);
			return $this->getJsonErrorResponse('json')->errorHandler(400, 
					$e->getMessage(), $e->getTrace());
		}
		
		return $response;
	}

	protected function hydrate (Lead $lead, $data = [])
	{
		$em = $this->getEntityManager();
		$leadAttributeRepository = $em->getRepository(
				"Lead\\Entity\\LeadAttribute");
		
		$accountRepository = $em->getRepository("Account\\Entity\\Account");
		
		if (isset($data['attributes'], $data['lead'])) {
			foreach ($data as $section => $values) {
				switch ($section) {
					case 'attributes':
						{
							foreach ($values as $attributeName => $attributeValue) {
								$leadAttribute = $leadAttributeRepository->findOneBy(
										[
												'attributeName' => $attributeName
										]);
								if (! $leadAttribute) {
									$leadAttribute = $leadAttributeRepository->findOneBy(
											[
													'attributeDesc' => $attributeName,
													'attributeName' => 'Question'
											]);
								}
								if (! $leadAttribute) {
									$leadAttribute = new LeadAttribute();
									$leadAttribute->setAttributeName('Question');
									$leadAttribute->setAttributeDesc(
											$attributeName);
								}
								if ($leadAttribute) {
									$leadAttributeValue = new LeadAttributeValue();
									
									$leadAttributeValue->setValue(
											$attributeValue);
									$leadAttributeValue->setAttribute(
											$leadAttribute);
									
									$lead->addAttribute($leadAttributeValue);
								}
							}
							break;
						}
					case 'lead':
						{
							foreach ($values as $property => $value) {
								switch ($property) {
									case 'timecreated':
									case 'referrer':
									case 'ipaddress':
										if ($property == 'timecreated') {
											$isvalid = $this->validateDate(
													$value);
											$value = $isvalid ? new \DateTime(
													$value) : new \DateTime(
													"now");
										}
										if (method_exists($lead, 
												'set' . ucfirst($property))) {
											$lead->{'set' . ucfirst($property)}(
													$value);
										}
										break;
									case 'company':
									case 'companyid':
										if (! $lead->getAccount()) {
											switch ($property) {
												case 'company':
													$criteria = [
															'name' => $value
													];
													break;
												case 'companyid':
													$criteria = [
															'id' => $value
													];
													break;
											}
											$account = $accountRepository->findOneBy(
													$criteria);
											if ($account) {
												$lead->setAccount($account);
											}
										}
										break;
								}
							}
							break;
						}
				}
			}
			
			return $lead;
		}
		
		return false;
	}

	public function getEntityClass ()
	{
		$module = $this->getModuleName();
		
		return "$module\Entity\\$module";
	}

	public function getEntityServiceClass ()
	{
		$module = $this->getModuleName();
		
		return "$module\Service\\$module";
	}

	protected function getModuleName ()
	{
		$module_array = explode('\\', get_class($this));
		
		return $module_array[0];
	}

	private function validateDate ($date)
	{
		$stamp = strtotime($date);
		if (! is_numeric($stamp))
			return false;
		$month = date('m', $stamp);
		$day = date('d', $stamp);
		$year = date('Y', $stamp);
		if (checkdate($month, $day, $year))
			return true;
		return false;
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

	protected function getLead ($id, $extract = true)
	{
		$em = $this->getEntityManager();
		
		$lead = $this->lead;
		if (! $lead) {
			$leadRepository = $em->getRepository("Lead\\Entity\\Lead");
			$lead = $leadRepository->findOneBy([
					'id' => $id
			]);
			$this->setLead($lead);
		}
		if ($lead) {
			if ($extract) {
				$hydrator = new DoctrineHydrator($em);
				return $hydrator->extract($lead);
			}
			return $lead;
		}
		return false;
	}

	/**
	 *
	 * @param \Lead\Entity\Lead $lead        	
	 */
	public function setLead ($lead)
	{
		$this->lead = $lead;
	}
	
	// Class Methods
	public function create ($data)
	{
		$this->createServiceEvent()
			->setEntityClass($this->getEntityClass())
			->setDescription("Lead Created");
		
		$result = null;
		
		$lead = $this->hydrate(new Lead(), $data);
		
		if ($lead) {
			$em = $this->getEntityManager();
			try {
				$em->persist($lead);
				$em->flush();
			} catch (\Exception $e) {
				$this->logError($e);
				return $this->getJsonErrorResponse('json')->errorHandler(400, 
						"Invalid Submission. " . $e->getMessage(), $data);
			}
		} else {
			$e = new \Exception('Failed to create lead.', 400);
			$this->logError($e, $data);
			return $this->getJsonErrorResponse('json')->errorHandler(400, 
					"Invalid Submission.", $data);
		}
		
		if ($lead) {
			$this->getServiceEvent()
				->setEntityId($lead->getId())
				->setMessage("Lead #" . $lead->getId() . " created.");
			$this->logEvent("AddAction.post");
			$result = $lead;
		}
		
		return $result;
	}

	public function getList ()
	{
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->add('select', 'e')->add('from', $this->getEntityClass() . ' e');
		
		return $qb->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
	}

	public function update ($id, $data)
	{
		$this->createServiceEvent()
			->setEntityId($id)
			->setEntityClass($this->getEntityClass())
			->setDescription("Lead Edited");
		$em = $this->getEntityManager();
		$leadRepository = $em->getRepository("Lead\\Entity\\Lead");
		
		$entity = $leadRepository->findOneBy([
				'id' => $id
		]);
		
		$hydrator = new DoctrineHydrator($em);
		try {
			$lead = $hydrator->hydrate($data, $entity);
			$em->persist($lead);
			$em->flush();
		} catch (\Exception $e) {
			$this->logError($e);
			return $this->getJsonErrorResponse('json')->errorHandler(400, 
					"Invalid Submission. " . $e->getMessage(), $data);
		}
		
		if ($lead) {
			$this->getServiceEvent()
				->setEntityId($lead->getId())
				->setMessage("Lead #" . $lead->getId() . " edited.");
			$this->logEvent("EditAction.post");
		}
		return $lead;
	}

	public function options ()
	{
		$response = $this->getResponse();
		// If in Options Array, Allow
		$response->getHeaders()->addHeaderLine('Allow', 
				implode(',', $this->allowMethods()));
		// Return Response
		return $response;
	}

	public function get ($id)
	{
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->add('select', 'e')
			->add('from', $this->getEntityClass() . ' e')
			->setMaxResults(1);
		
		$result = $qb->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
		return $result ? current($result) : false;
	}
	
	// Override default actions as they do not return valid JsonModels
	public function delete ($id)
	{
		return $this->getJsonErrorResponse('json')->methodNotAllowed();
	}

	public function deleteList ($data)
	{
		return $this->getJsonErrorResponse('json')->methodNotAllowed();
	}

	public function head ($id = null)
	{
		return $this->getJsonErrorResponse('json')->methodNotAllowed();
	}

	public function patch ($id, $data)
	{
		return $this->getJsonErrorResponse('json')->methodNotAllowed();
	}

	public function replaceList ($data)
	{
		return $this->getJsonErrorResponse('json')->methodNotAllowed();
	}

	public function patchList ($data)
	{
		return $this->getJsonErrorResponse('json')->methodNotAllowed();
	}
}
