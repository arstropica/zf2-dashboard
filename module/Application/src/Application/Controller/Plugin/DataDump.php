<?php
namespace Application\Controller\Plugin;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @author arstropica
 *        
 */
class DataDump extends AbstractPlugin implements ServiceLocatorAwareInterface
{
	use ServiceLocatorAwareTrait;

	protected $method;

	public function __construct (ServiceLocatorInterface $serviceLocator, 
			$method)
	{
		$this->setServiceLocator($serviceLocator);
		$this->method = $method;
	}

	public function __invoke ($arg)
	{
		if (method_exists($this, $this->method))
			return $this->{$this->method}($arg);
		else
			return $this->logConsole($arg);
	}

	public function logConsole ($string)
	{
		echo "<script>console.log(" . json_encode($string) . ");</script>\n\n";
		return $this;
	}

	public function dumpConsole ($mixed)
	{
		echo "<script>console.dir(" . json_encode($mixed) . ");</script>\n\n";
		return $this;
	}

	public function preDump ($mixed)
	{
		echo "<pre>" . print_r($mixed, true) . "</pre>\n\n";
		return $this;
	}

	public function varDump ($mixed)
	{
		echo var_export($mixed, true) . "\n\n";
		return $this;
	}
}

?>