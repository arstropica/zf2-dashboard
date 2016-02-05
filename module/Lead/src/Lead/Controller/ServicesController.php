<?php

namespace Lead\Controller;

use Lead\Entity\Lead;
use Zend\View\Model\JsonModel;
use Event\Entity\Event;
use Api\Entity\Api;
use Application\Provider\EntityManagerAwareTrait;
use Application\Provider\ServiceEventTrait;
use Zend\Mvc\Controller\AbstractRestfulController;

/**
 *
 * @author arstropica
 *        
 */
class ServicesController extends AbstractRestfulController {
	use EntityManagerAwareTrait, ServiceEventTrait;

	public function processAction()
	{
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', 0);
		
		if ($id) {
			$result = $this->submit($id);
			
			return $this->getJsonErrorResponse('json')
				->successOperation($result);
		}
		
		return $this->getJsonErrorResponse('json')
			->errorHandler(400, "Operation Failed. Lead " . ($id ?  : " not ") . " submitted.");
	}

	protected function submit($id)
	{
		$results = [ ];
		$entity = $this->getLead($id);
		if ($entity && $entity instanceof Lead) {
			$account = $entity->getAccount();
			if ($account) {
				$apis = $account->getApis(true);
				if ($apis->count() > 0) {
					try {
						foreach ( $apis as $api ) {
							$result = false;
							switch ($api->getName()) {
								case 'Tenstreet' :
									$result = $this->getServiceLocator()
										->get('TenStreet\Service\PostClientData')
										->send($id);
									break;
								case 'Email' :
									$result = $this->getServiceLocator()
										->get('Email\Service\SendMail')
										->send($id, 'Email');
									break;
								case 'WebWorks' :
									$result = $this->getServiceLocator()
										->get('WebWorks\Service\ImportXML')
										->send($id, 'WebWorks');
									break;
							}
							if ($result instanceof JsonModel) {
								$result = $result->getVariables();
							}
							$results = array_merge_recursive($result, $results);
						}
					} catch ( \Exception $e ) {
						$result = [ ];
						$result ['error'] [] = [ 
								$e->getMessage(),
								$e->getTrace() 
						];
						$results = array_merge_recursive($results, $result);
					}
				}
			}
		}
		
		return $results;
	}

	/**
	 *
	 * @param int $id        	
	 * @return Lead|null
	 */
	protected function getLead($id)
	{
		$em = $this->getEntityManager();
		$leadRepository = $em->getRepository("Lead\\Entity\\Lead");
		
		return $leadRepository->findOneBy([ 
				'id' => $id 
		]);
	}
}

?>