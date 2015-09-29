<?php
namespace TenStreet\Service;
use Zend\EventManager\EventManagerAwareInterface;

/**
 *
 * @author arstropica
 *        
 */
class PostClientDataService extends AbstractTenStreetService implements 
		EventManagerAwareInterface
{

	/**
	 * (non-PHPdoc)
	 *
	 * @see \TenStreet\Service\AbstractTenStreetService::send()
	 */
	public function send ($id)
	{
		$this->getServiceEvent()->setEntityId($id)->setEntityClass(
				'Lead\Entity\Lead')->setDescription('TenStreet Submission');
		
		if (! $this->checkAuth()) {
			return $this->respondError(
					new \Exception('Insufficient User Authorization.', 401));
		}
		
		$result = $clientId = $password = $service = null;
		$options = $this->getOptions($id);
		$this->getServiceEvent()->setParams($options);
		extract($options);
		if (isset($clientId, $password, $service)) {
			$xml_data = $this->getData($id);
			try {
				$response = $this->PostClientData($id, $xml_data, $clientId, 
						$password, $service);
				$result = $this->parse($response);
			} catch (\Exception $e) {
				return $this->respondError($e);
			}
		} else {
			return $this->respondError(
					new \Exception('Insufficient API Authorization.', 401));
		}
		return $this->respondSuccess($result);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \TenStreet\Service\AbstractTenStreetService::getOptions()
	 */
	public function getOptions ($id)
	{
		$options = [];
		$credentials = $this->getTenStreetDataMapper()->getCredentials();
		
		$options['clientId'] = $credentials->getClientId();
		$options['password'] = $credentials->getPassword();
		$options['service'] = $credentials->getService();
		
		return $options;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \TenStreet\Service\AbstractTenStreetService::getData()
	 */
	public function getData ($id)
	{
		$tenStreetData = $this->getTenStreetDataMapper()->getById($id);
		$this->setTenStreetData($tenStreetData);
		
		try {
			$data = $this->getServiceLocator()
				->get('HydratorManager')
				->get('TenStreetDataHydrator')
				->extract($tenStreetData);
		} catch (\Exception $e) {
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
	 * @see \TenStreet\Service\AbstractTenStreetService::logEvent()
	 */
	public function logEvent ($event)
	{
		$this->getEventManager()->trigger($event, $this->getServiceEvent());
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \TenStreet\Service\AbstractTenStreetService::respond()
	 */
	public function respond ($data = null)
	{
		return [
				'tenstreet' => $data
		];
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \TenStreet\Service\AbstractTenStreetService::respondError()
	 */
	public function respondError (\Exception $e)
	{
		$this->getServiceEvent()->setIsError(true);
		$this->getServiceEvent()->setMessage($e->getMessage());
		$this->getServiceEvent()->setResult($e->getTraceAsString());
		$this->logEvent('RuntimeError');
		return $this->respond(
				$this->errorResponse->errorHandler($e->getCode(), 
						$e->getMessage(), null, $e->getTraceAsString()));
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \TenStreet\Service\AbstractTenStreetService::respondSuccess()
	 */
	public function respondSuccess ($result)
	{
		$this->getServiceEvent()->setMessage(
				$result['lastresponse'] ?  : 'Unknown Response')->setOutcome(
				$result['submitted'] ?  : 0);
		$this->logEvent('PostClientData.post');
		return $this->respond($this->errorResponse->successOperation($result));
	}

	public function PostClientData ($id, $xml_data, $clientId, $password, 
			$service)
	{
		$xml_response = null;
		
		try {
			$xml_response = $this->getClient()->PostClientData($xml_data, 
					$clientId, $password, $service);
		} catch (\SoapFault $e) {
			$message = $this->client->getLastResponse();
			if (strlen($message)) {
				$response = $this->xml2array($message);
				$this->getServiceEvent()->setResult($response);
				return $response;
			} else {
				return $this->respondError($e);
			}
		}
		$response = $this->xml2array($xml_response);
		$this->getServiceEvent()->setResult($response);
		
		return $response;
	}
}
