<?php

namespace WebWorks\Service;

use User\Provider\AuthorizationAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Api\Service\ApiServiceInterface;
use Zend\EventManager\EventManagerAwareTrait;
use WebWorks\Mapper\WebWorksDataMapper;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
use Application\Controller\Plugin\JSONErrorResponse;
use WebWorks\Entity\WebWorksData;
use Application\Provider\easyXMLTrait;
use Application\Event\ServiceEvent;
use Zend\EventManager\EventManagerAwareInterface;
use Application\Provider\ServiceEventTrait;

/**
 *
 * @author arstropica
 *        
 */
abstract class AbstractWebWorksService implements ServiceLocatorAwareInterface, ApiServiceInterface, EventManagerAwareInterface {
	
	use EventManagerAwareTrait, ServiceLocatorAwareTrait, AuthorizationAwareTrait, easyXMLTrait, ServiceEventTrait;
	
	/**
	 *
	 * @var JSONErrorResponse
	 */
	protected $errorResponse;
	
	/**
	 *
	 * @var array
	 */
	protected $endPoint;
	
	/**
	 *
	 * @var string
	 */
	protected $env;
	
	/**
	 *
	 * @var Client
	 */
	protected $client;
	
	/**
	 *
	 * @var string
	 */
	protected $rootNode;
	
	/**
	 *
	 * @var WebWorksData
	 */
	protected $webWorksData;

	public function __construct(ServiceLocatorInterface $servicelocator)
	{
		$this->setServiceLocator($servicelocator);
		$this->errorResponse = $this->getServiceLocator()
			->get('ControllerPluginManager')
			->get('getJsonErrorResponse');
		$this->errorResponse->setMode('array');
		$this->setServiceEvent(new ServiceEvent('WebWorks', $this));
	}

	/**
	 *
	 * @return the $errorResponse
	 */
	public function getErrorResponse()
	{
		return $this->errorResponse;
	}

	/**
	 *
	 * @param \Application\Controller\Plugin\JSONErrorResponse $errorResponse        	
	 */
	public function setErrorResponse($errorResponse)
	{
		$this->errorResponse = $errorResponse;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::send()
	 */
	public function send($id, $service)
	{}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::logEvent()
	 */
	public function logEvent($event)
	{}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::getOptions()
	 */
	public function getOptions($id, $service)
	{}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::getData()
	 */
	public function getData($id)
	{}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::respond()
	 */
	public function respond($data = null)
	{}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::respondError()
	 */
	public function respondError(\Exception $e)
	{}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::respondSuccess()
	 */
	public function respondSuccess($result)
	{}

	/**
	 *
	 * @param string $env        	
	 *
	 * @return string $endpoint
	 */
	public function getEndPoint()
	{
		if (!$this->endPoint) {
			$config = $this->getServiceLocator()
				->get('Config');
			$this->setEndPoint($config ['WebWorks'] ['Controller'] ['Client'] ['endpoint']);
		}
		return $this->endPoint [$this->getEnv()];
	}

	/**
	 *
	 * @param string $endPoint        	
	 */
	public function setEndPoint($endPoint)
	{
		$this->endPoint = $endPoint;
	}

	/**
	 *
	 * @return the $env
	 */
	public function getEnv()
	{
		if (!$this->env) {
			$this->setEnv(getenv('APPLICATION_ENV') ?  : 'development');
		}
		return $this->env;
	}

	/**
	 *
	 * @param string $env        	
	 */
	public function setEnv($env)
	{
		$this->env = $env;
	}

	/**
	 *
	 * @return the $client
	 */
	public function getClient()
	{
		if (!$this->client) {
			$client = new Client();
			$client->setAdapter(new Curl());
			$this->setClient($client);
		}
		
		return $this->client;
	}

	/**
	 *
	 * @param Client $client        	
	 */
	public function setClient($client)
	{
		$this->client = $client;
	}

	/**
	 *
	 * @return the $rootNode
	 */
	public function getRootNode()
	{
		if (!$this->rootNode) {
			$config = $this->getServiceLocator()
				->get('Config');
			$this->setRootNode($config ['WebWorks'] ['Controller'] ['Client'] ['rootNode']);
		}
		return $this->rootNode;
	}

	/**
	 *
	 * @param string $rootNode        	
	 */
	public function setRootNode($rootNode)
	{
		$this->rootNode = $rootNode;
	}

	/**
	 *
	 * @return the $webWorksData
	 */
	public function getWebWorksData()
	{
		return $this->webWorksData;
	}

	/**
	 *
	 * @param \WebWorks\Entity\WebWorksData $webWorksData        	
	 */
	public function setWebWorksData($webWorksData)
	{
		$this->webWorksData = $webWorksData;
	}

	/**
	 * Check Authorization
	 *
	 * @return boolean
	 */
	public function checkAuth()
	{
		if (!$this->authorize() && !$this->isUserAuthorized()) {
			return false;
		}
		return true;
	}

	/**
	 *
	 * @return WebWorksDataMapper
	 */
	protected function getWebWorksDataMapper()
	{
		$sm = $this->getServiceLocator();
		return $sm->get('WebWorksDataMapper');
	}

	protected function parse($response)
	{
		$result = array (
				'submitted' => 0 
		);
		
		if (isset($response ['AppManagerResponse'])) {
			$r = $response ['AppManagerResponse'];
			$result ['lastresponse'] = $r ['Description'];
			if (isset($r ['Status'])) {
				$result ['submitted'] = $r ['Status'] == 'Accepted' ? 1 : 0;
				if ($result ['submitted']) {
					$result ['timesubmitted'] = date('Y-m-d H:i:s', strtotime($r ['DateTime']));
					$result ['driverid'] = $r ['DriverId'];
				}
			}
		}
		
		return $result;
	}
}

?>