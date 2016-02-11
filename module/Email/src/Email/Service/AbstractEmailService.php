<?php

namespace Email\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Api\Service\ApiServiceInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use User\Provider\AuthorizationAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Controller\Plugin\JSONErrorResponse;
use Application\Event\ServiceEvent;
use Lead\Entity\Lead;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Application\Provider\EntityManagerAwareTrait;
use Application\Provider\ServiceEventTrait;

/**
 *
 * @author arstropica
 *        
 */
abstract class AbstractEmailService implements ServiceLocatorAwareInterface, ApiServiceInterface, EventManagerAwareInterface {
	
	use EventManagerAwareTrait, ServiceLocatorAwareTrait, AuthorizationAwareTrait, EntityManagerAwareTrait, ServiceEventTrait;
	
	/**
	 *
	 * @var JSONErrorResponse
	 */
	protected $errorResponse;
	
	/**
	 *
	 * @var Lead
	 */
	protected $lead;

	public function __construct(ServiceLocatorInterface $servicelocator)
	{
		$this->setServiceLocator($servicelocator);
		$this->errorResponse = $this->getServiceLocator()
			->get('ControllerPluginManager')
			->get('getJsonErrorResponse');
		$this->errorResponse->setMode('array');
		$this->setServiceEvent(new ServiceEvent('Email', $this));
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::respondError()
	 *
	 */
	public function respondError(\Exception $e)
	{}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::getOptions()
	 *
	 */
	public function getOptions($id, $service = 'Email')
	{}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::respond()
	 *
	 */
	public function respond($data = null)
	{}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::send()
	 *
	 */
	public function send($id, $service = 'Email')
	{}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::respondSuccess()
	 *
	 */
	public function respondSuccess($result)
	{}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::getData()
	 *
	 */
	public function getData($id)
	{}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Api\Service\ApiServiceInterface::logEvent()
	 *
	 */
	public function logEvent($event)
	{}

	/**
	 *
	 * @return \Application\Controller\Plugin\JSONErrorResponse
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

	protected function getLead($id, $extract = true)
	{
		$em = $this->getEntityManager();
		
		$lead = $this->lead;
		if (!$lead || ($lead instanceof Lead && $lead->getId() != $id)) {
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
	public function setLead($lead)
	{
		$this->lead = $lead;
	}

	protected function getBody($leadData, $html = true)
	{
		$data = [ ];
		$content = "";
		
		foreach ( $leadData as $property => $asset ) {
			if ($asset) {
				switch ($property) {
					case 'id' :
						// case 'referrer' :
						$data ['summary'] [$property] = $asset;
						break;
					case 'ipaddress' :
						$data ['summary'] ["IP Address"] = $asset;
						break;
					case 'timecreated' :
						$data ['summary'] ["Time Created"] = date_format($asset, 'Y-m-d H:i:s');
						break;
					case 'account' :
						$data ['summary'] [$property] = $asset->getName();
						break;
					case 'attributes' :
						foreach ( $asset as $attributeValue ) {
							$data ['details'] [$attributeValue->getAttribute()
								->getAttributeDesc()] = $attributeValue->getValue();
						}
						break;
				}
			}
		}
		
		if ($data) {
			if ($html) {
				$template = <<<HTML
				<h1>Lead Report</h1>
				%1\$s
HTML;
				$table = <<<HTML
				<h2>%1\$s</h2>
				<table>%2\$s</table>
HTML;
				$TR = <<<HTML
				<tr><th align="left">%1\$s</th><td>%2\$s</td></tr>
HTML;
			} else {
				$template = <<<TEXT
Lead Report
===================================
===================================

%1\$s
TEXT;
				$table = <<<TEXT

%1\$s
===================================
%2\$s
TEXT;
				$TR = "%1\$s : %2\$s\r\n";
			}
			$outline = "";
			foreach ( $data as $section => $values ) {
				$inline = "";
				foreach ( $values as $property => $value ) {
					$inline .= sprintf($TR, ucwords($property), $value);
				}
				$outline .= sprintf($table, ucwords($section), $inline);
			}
			$content = sprintf($template, $outline);
		}
		return $content;
	}
}

?>