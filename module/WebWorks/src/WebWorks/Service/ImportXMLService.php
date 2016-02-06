<?php

namespace WebWorks\Service;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\Http\Request;

/**
 *
 * @author arstropica
 *        
 */
class ImportXMLService extends AbstractWebWorksService implements EventManagerAwareInterface {

	/**
	 * (non-PHPdoc)
	 *
	 * @see \WebWorks\Service\AbstractWebWorksService::send()
	 */
	public function send($id, $service = "WebWorks")
	{
		$this->getServiceEvent()
			->setEntityId($id)
			->setEntityClass('Lead\Entity\Lead')
			->setDescription('WebWorks Submission');
		
		if (!$this->checkAuth()) {
			return $this->respondError(new \Exception('Insufficient User Authorization.', 401));
		}
		
		$result = null;
		$xml_data = $this->getData($id);
		try {
			$response = $this->ImportXML($id, $xml_data);
			$result = $this->parse($response);
		} catch ( \Exception $e ) {
			return $this->respondError($e);
		}
		return $this->respondSuccess($result);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \WebWorks\Service\AbstractWebWorksService::getOptions()
	 */
	public function getOptions($id, $service = "WebWorks")
	{
		$options = [ ];
		return $options;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \WebWorks\Service\AbstractWebWorksService::getData()
	 */
	public function getData($id)
	{
		$webWorksData = $this->getWebWorksDataMapper()
			->getById($id);
		$this->setWebWorksData($webWorksData);
		
		try {
			$data = $this->getServiceLocator()
				->get('HydratorManager')
				->get('WebWorks\Hydrator\WebWorksDataHydrator')
				->extract($webWorksData);
		} catch ( \Exception $e ) {
			var_dump($e->getMessage());
			throw new \Exception("Could not retrieve data for Lead {$id}.");
			return;
		}
		
		if ($data) {
			return $this->array2xml($this->getRootNode(), $data, false);
		} else {
			throw new \Exception("Could not retrieve data for Lead {$id}.");
		}
		return null;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \WebWorks\Service\AbstractWebWorksService::logEvent()
	 */
	public function logEvent($event)
	{
		$this->getEventManager()
			->trigger($event, $this->getServiceEvent());
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \WebWorks\Service\AbstractWebWorksService::respond()
	 */
	public function respond($data = null)
	{
		return [ 
				'webworks' => $data 
		];
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \WebWorks\Service\AbstractWebWorksService::respondError()
	 */
	public function respondError(\Exception $e)
	{
		$this->getServiceEvent()
			->setIsError(true);
		$this->getServiceEvent()
			->setMessage($e->getMessage());
		$this->getServiceEvent()
			->setResult($e->getTraceAsString());
		$this->logEvent('RuntimeError');
		return $this->respond($this->errorResponse->errorHandler($e->getCode(), $e->getMessage(), null, $e->getTraceAsString()));
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \WebWorks\Service\AbstractWebWorksService::respondSuccess()
	 */
	public function respondSuccess($result)
	{
		$this->getServiceEvent()
			->setMessage(isset($result ['lastresponse']) ? $result ['lastresponse'] : 'Unknown Response')
			->setOutcome(isset($result ['submitted']) ? $result ['submitted'] : 0);
		$this->logEvent('ImportXML.post');
		return $this->respond($this->errorResponse->successOperation($result));
	}

	public function ImportXML($id, $xml_data)
	{
		$zend_response = null;
		$xml_response = null;
		$post_data = array (
				'xml' => $xml_data 
		);
		$post_content = http_build_query($post_data);
		
		try {
			$client = $this->getClient();
			$request = new Request();
			$url = $this->getEndPoint();
			
			$request->setUri($url);
			$request->setMethod(Request::METHOD_POST);
			$request->setContent($post_content);
			
			/* @var $zend_response \Zend\Http\Response */
			$zend_response = $client->dispatch($request);
			if ($zend_response) {
				$xml_response = $zend_response->getContent();
			}
		} catch ( \Exception $e ) {
			return $this->respondError($e);
		}
		
		try {
			$response = $this->xml2array($xml_response);
		} catch ( \Exception $e ) {
			$response = $xml_response;
		}
		$this->getServiceEvent()
			->setResult($response);
		
		return $response;
	}

}

?>