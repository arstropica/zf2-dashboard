<?php

namespace TenStreet\Service;

use User\Provider\AuthorizationAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Api\Service\ApiServiceInterface;
use Zend\EventManager\EventManagerAwareTrait;
use TenStreet\Mapper\TenStreetDataMapper;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Soap\Client;
use Application\Controller\Plugin\JSONErrorResponse;
use TenStreet\Entity\TenStreetData;
use Application\Provider\easyXMLTrait;
use Application\Event\ServiceEvent;
use Zend\EventManager\EventManagerAwareInterface;
use Application\Provider\ServiceEventTrait;

/**
 *
 * @author arstropica
 *        
 */
abstract class AbstractTenStreetService implements ServiceLocatorAwareInterface, ApiServiceInterface, EventManagerAwareInterface {
	
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
	protected $wsdl;
	
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
	 * @var TenStreetData
	 */
	protected $tenStreetData;
	
	public function __construct(ServiceLocatorInterface $servicelocator) {
		$this->setServiceLocator( $servicelocator );
		$this->errorResponse = $this->getServiceLocator()
			->get( 'ControllerPluginManager' )
			->get( 'getJsonErrorResponse' );
		$this->errorResponse->setMode( 'array' );
		$this->setServiceEvent( new ServiceEvent( 'TenStreet', $this ) );
	}
	
	/**
	 *
	 * @return the $errorResponse
	 */
	public function getErrorResponse() {
		return $this->errorResponse;
	}
	
	/**
	 *
	 * @param \Application\Controller\Plugin\JSONErrorResponse $errorResponse        	
	 */
	public function setErrorResponse($errorResponse) {
		$this->errorResponse = $errorResponse;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::send()
	 */
	public function send($id, $service) {
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::logEvent()
	 */
	public function logEvent($event) {
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::getOptions()
	 */
	public function getOptions($id, $service) {
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::getData()
	 */
	public function getData($id) {
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::respond()
	 */
	public function respond($data = null) {
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::respondError()
	 */
	public function respondError(\Exception $e) {
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::respondSuccess()
	 */
	public function respondSuccess($result) {
	}
	
	/**
	 *
	 * @param string $env        	
	 *
	 * @return string $wsdl
	 */
	public function getWsdl() {
		if (! $this->wsdl) {
			$config = $this->getServiceLocator()
				->get( 'Config' );
			$this->setWsdl( $config ['TenStreet'] ['Controller'] ['SoapClient'] ['wsdl'] );
		}
		return $this->wsdl [$this->getEnv()];
	}
	
	/**
	 *
	 * @param string $wsdl        	
	 */
	public function setWsdl($wsdl) {
		$this->wsdl = $wsdl;
	}
	
	/**
	 *
	 * @return the $env
	 */
	public function getEnv() {
		if (! $this->env) {
			$this->setEnv( getenv( 'APPLICATION_ENV' ) ?  : 'development' );
		}
		return $this->env;
	}
	
	/**
	 *
	 * @param string $env        	
	 */
	public function setEnv($env) {
		$this->env = $env;
	}
	
	/**
	 *
	 * @return the $client
	 */
	public function getClient() {
		if (! $this->client) {
			$clientOptions = array (
					'compression' => SOAP_COMPRESSION_ACCEPT,
					'soap_version' => SOAP_1_1,
					'connection_timeout' => 5 
			);
			
			$client = new Client( $this->getWsdl( $this->getEnv() ), $clientOptions );
			$this->setClient( $client );
		}
		
		return $this->client;
	}
	
	/**
	 *
	 * @param \Zend\Soap\Client $client        	
	 */
	public function setClient($client) {
		$this->client = $client;
	}
	
	/**
	 *
	 * @return the $rootNode
	 */
	public function getRootNode() {
		if (! $this->rootNode) {
			$config = $this->getServiceLocator()
				->get( 'Config' );
			$this->setRootNode( $config ['TenStreet'] ['Controller'] ['SoapClient'] ['rootNode'] );
		}
		return $this->rootNode;
	}
	
	/**
	 *
	 * @param string $rootNode        	
	 */
	public function setRootNode($rootNode) {
		$this->rootNode = $rootNode;
	}
	
	/**
	 *
	 * @return the $tenStreetData
	 */
	public function getTenStreetData() {
		return $this->tenStreetData;
	}
	
	/**
	 *
	 * @param \TenStreet\Entity\TenStreetData $tenStreetData        	
	 */
	public function setTenStreetData($tenStreetData) {
		$this->tenStreetData = $tenStreetData;
	}
	
	/**
	 * Check Authorization
	 *
	 * @return boolean
	 */
	public function checkAuth() {
		if (! $this->authorize() && ! $this->isUserAuthorized()) {
			return false;
		}
		return true;
	}
	
	/**
	 *
	 * @return TenStreetDataMapper
	 */
	protected function getTenStreetDataMapper() {
		$sm = $this->getServiceLocator();
		return $sm->get( 'TenStreetDataMapper' );
	}
	
	protected function parse($response) {
		$result = array (
				'submitted' => 0 
		);
		
		if (isset( $response ['TenstreetResponse'] )) {
			$r = $response ['TenstreetResponse'];
			$result ['lastresponse'] = $r ['Description'];
			if (isset( $r ['Status'] )) {
				$result ['submitted'] = $r ['Status'] == 'Accepted' ? 1 : 0;
				if ($result ['submitted']) {
					$result ['timesubmitted'] = date( 'Y-m-d H:i:s', strtotime( $r ['DateTime'] ) );
					$result ['driverid'] = $r ['DriverId'];
				}
			}
		}
		
		return $result;
	}
}

?>