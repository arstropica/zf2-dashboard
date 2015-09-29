<?php
namespace Application\Controller\Plugin;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\Exception\DomainException;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ErrorResponse extends AbstractPlugin implements 
		ServiceLocatorAwareInterface
{
	
	use ServiceLocatorAwareTrait;

	protected $messenger;

	protected $event;

	public function __construct (ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
		$this->setController(
				$serviceLocator->get('ControllerPluginManager')
					->getController());
		$this->setMessenger($this->getController());
	}

	public function __invoke ()
	{}

	public function setMessenger ($controller = null)
	{
		if (! $this->messenger) {
			if (! $controller) {
				$controller = $this->getController();
				if (! $controller instanceof InjectApplicationEventInterface) {
					throw new DomainException(
							get_class($this) .
									 ' requires a controller that implements InjectApplicationEventInterface');
				}
			}
			if ($controller) {
				$this->messenger = $controller->flashMessenger();
			} else {
				throw new \Exception(
						"Controller could not be found in " . get_class($this));
			}
		}
		return $this->messenger;
	}

	public function addMessages ($defaults = array(), $errors = array(), $warnings = array(), 
			$infos = array())
	{
		$args = array(
				"default" => $defaults,
				"error" => $errors,
				"warning" => $warnings,
				"info" => $infos
		);
		$this->setMessenger();
		
		foreach ($args as $type => $messages) {
			if (is_array($messages) && $messages) {
				$messages = $this->arrayFlatten($messages);
				foreach ($messages as $message) {
					switch ($type) {
						case "default":
							$this->messenger->addMessage($message);
							break;
						case "error":
							$this->messenger->addErrorMessage($message);
							break;
						case "warning":
							$this->messenger->addWarningMessage($message);
							break;
						case "info":
							$this->messenger->addInfoMessage($message);
							break;
						default:
							$this->messenger->addMessage($message);
							break;
					}
				}
			}
		}
	}

	public function addMessage ($message, $type = "default")
	{
		$this->setMessenger();
		
		switch ($type) {
			case "default":
				$this->messenger->addMessage($message);
				break;
			case "error":
				$this->messenger->addErrorMessage($message);
				break;
			case "warning":
				$this->messenger->addWarningMessage($message);
				break;
			case "info":
				$this->messenger->addInfoMessage($message);
				break;
			default:
				$this->messenger->addMessage($message);
				break;
		}
	}

	public function arrayFlatten ($array)
	{
		$return = array();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$return = array_merge($return, $this->arrayFlatten($value));
			} else {
				$return[$key] = $value;
			}
		}
		return $return;
	}

	/**
	 * Get the event
	 *
	 * @return MvcEvent
	 * @throws DomainException if unable to find event
	 */
	protected function getEvent ()
	{
		if ($this->event) {
			return $this->event;
		}
		
		$controller = $this->getController();
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new DomainException(
					get_class($this) .
							 ' requires a controller that implements InjectApplicationEventInterface');
		}
		
		$event = $controller->getEvent();
		if (! $event instanceof MvcEvent) {
			$params = $event->getParams();
			$event = new MvcEvent();
			$event->setParams($params);
		}
		$this->event = $event;
		
		return $this->event;
	}
}