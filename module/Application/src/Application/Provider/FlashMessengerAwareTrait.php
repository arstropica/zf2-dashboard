<?php

namespace Application\Provider;

use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @author arstropica
 *        
 */
trait FlashMessengerAwareTrait {
	
	/**
	 *
	 * @var FlashMessenger
	 */
	protected $flashMessenger;

	public function getFlashMessenger()
	{
		$flashMessenger = isset($this->flashMessenger) ? $this->flashMessenger : null;
		if (null == $flashMessenger) {
			$flashMessenger = $this->getServiceLocator()
				->get('ControllerPluginManager')
				->get('flashMessenger');
			if ($flashMessenger) {
				$this->flashMessenger = $flashMessenger;
			}
		}
		return $this->flashMessenger;
	}

	public function setFlashMessenger(FlashMessenger $flashMessenger)
	{
		$this->flashMessenger = $flashMessenger;
	}

	abstract public function getServiceLocator();

	abstract public function setServiceLocator(ServiceLocatorInterface $serviceLocator);

}

?>