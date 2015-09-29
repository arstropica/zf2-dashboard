<?php
namespace Application\Controller\Plugin;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\JsonModel;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\Exception\DomainException;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class JSONErrorResponse extends AbstractPlugin implements 
		ServiceLocatorAwareInterface
{
	
	use ServiceLocatorAwareTrait;

	protected $event;

	protected static $mode;

	public function __construct (ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
		$this->setController(
				$serviceLocator->get('ControllerPluginManager')
					->getController());
	}

	public function __invoke ($mode = 'array')
	{
		self::$mode = $mode;
		return $this;
	}

	public function setMode ($mode)
	{
		self::$mode = $mode;
		
		return $this;
	}

	public function getMode ()
	{
		return self::$mode;
	}

	public function unknownError (\Exception $e = null, $fullTrace = false)
	{
		$trace = "";
		if ($e instanceof \Exception) {
			$trace = $fullTrace ? $e->getTrace() : $e->getTraceAsString();
		}
		return $this->errorHandler(422, 'Your request could not be processed.', 
				$trace);
	}

	public function insufficientAuthorization ()
	{
		return $this->errorHandler(401, 'Not Authorized.');
	}

	public function missingParameter ()
	{
		return $this->errorHandler(422, 'One or more parameters are missing.');
	}

	public function methodNotAllowed ()
	{
		return $this->errorHandler(405, 'Method Not Allowed.');
	}

	public function successOperation ($data)
	{
		return $this->responseHandler(201, $data);
	}

	public function responseHandler ($code, $data = null, $mode = false)
	{
		if ($data instanceof JsonModel) {
			return $data;
		}
		$response = $this->getResponse();
		$response->setStatusCode($code);
		$request = $this->getRequest();
		$post = $request->getPost();
		
		$output = array(
				'code' => $code,
				'data' => $data,
				'request' => $post
		);
		if ($mode) {
			return $mode == 'json' ? new JsonModel($output) : $output;
		} else {
			return self::$mode == 'json' ? new JsonModel($output) : $output;
		}
	}

	public function errorHandler ($code, $msg = 'An unspecified Error occurred.', $data = null, $trace = '')
	{
		$output = $this->responseHandler($code, $data, 'array');
		
		if ($output instanceof JsonModel) {
			return $output;
		}
		$output['error'] = $msg;
		$output['trace'] = $trace;
		
		return self::$mode == 'json' ? new JsonModel($output) : $output;
	}

	protected function getResponse ()
	{
		$controller = $this->getController();
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new DomainException(
					get_class($this) .
							 ' requires a controller that implements InjectApplicationEventInterface');
		}
		return $controller->getResponse();
	}

	protected function getRequest ()
	{
		$controller = $this->getController();
		if (! $controller instanceof InjectApplicationEventInterface) {
			throw new DomainException(
					get_class($this) .
							 ' requires a controller that implements InjectApplicationEventInterface');
		}
		return $controller->getRequest();
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