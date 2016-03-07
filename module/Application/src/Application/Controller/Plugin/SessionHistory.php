<?php

namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Service\SessionHistoryService;

/**
 *
 * @author arstropica
 *        
 */
class SessionHistory extends AbstractPlugin implements ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $parentLocator;
	
	/**
	 *
	 * @var SessionHistoryService
	 */
	protected $service;

	public function __construct(ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
		$this->parentLocator = $serviceLocator;
		$this->service = $serviceLocator->get('Application\Service\Factory\SessionHistoryServiceFactory');
	}

	public function __invoke($url = null)
	{
		$result = false;
		if ($url) {
			$this->service->setHistory($url);
		}
		return $this->service->getHistory();
	}
}

?>