<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Application\Controller\Plugin\BodyClasses;
use Application\View\Helper\FlashMessenger;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Application\Event\Listener\AggregateListener;
use Application\Controller\Plugin\DataDump;

class Module implements AutoloaderProviderInterface
{

	public function onBootstrap (MvcEvent $e)
	{
		$sm = $e->getApplication()->getServiceManager();
		$app_config = $sm->get('config');
		$app_options = $app_config['app_options'];
		
		if (array_key_exists('recover_from_fatal', $app_options) &&
				 $app_options['recover_from_fatal']) {
			$redirect_url = $app_options['redirect_url'];
			$callback = null;
			if (array_key_exists('fatal_errors_callback', $app_options) &&
					 $app_options['fatal_errors_callback']) {
				$callback = $app_options['fatal_errors_callback'];
			}
			register_shutdown_function(
					array(
							'Application\Module',
							'handleFatalPHPErrors'
					), $redirect_url, $callback);
		}
		
		set_error_handler(
				array(
						'Application\Module',
						'handlePHPErrors'
				));
		
		foreach ($app_options['php_settings'] as $key => $value) {
			ini_set($key, $value);
		}
		
		$eventManager = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		
		$logger = $sm->get('Logger');
		$eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, 
				function  (MvcEvent $e) use( $logger)
				{
					$logger->info(
							'An Exception has occurred. ' .
									 $e->getResult()->exception->getMessage());
				}, - 200);
		
		$sm->get('viewhelpermanager')->setFactory('EntityNav', 
				function  ($sm) use( $e)
				{
					$viewHelper = new View\Helper\EntityNavSelect(
							$e->getRouteMatch());
					return $viewHelper;
				});
		
		$eventManager->attach(new AggregateListener($sm));
	}

	public function getConfig ()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig ()
	{
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/src/' .
										 __NAMESPACE__
						)
				)
		);
	}

	public static function handlePHPErrors ($i_type, $s_message, $s_file, 
			$i_line)
	{
		if (! ($i_type && error_reporting())) {
			return;
		}
		
		throw new \Exception(
				"Error: " . $s_message . " in file " . $s_file . " at line " .
						 $i_line);
	}

	public static function handleFatalPHPErrors ($redirect_url, $callback = null)
	{
		if (php_sapi_name() != 'cli' && (($e = @error_get_last()) !== null) &&
				 (is_array($e))) {
			if (null != $callback) {
				$code = isset($e['type']) ? $e['type'] : 0;
				$msg = isset($e['message']) ? $e['message'] : '';
				$file = isset($e['file']) ? $e['file'] : '';
				$line = isset($e['line']) ? $e['line'] : '';
				$callback($msg, $file, $line);
			}
			header("Location: " . $redirect_url);
		}
		return false;
	}

	public function getServiceConfig ()
	{
		return array(
				'factories' => array(
						'Logger' => function  ($sm)
						{
							$config = $sm->get('config');
							$logger = new \Zend\Log\Logger();
							if (isset($config['log']['file']) && is_writable(
									dirname($config['log']['file']))) {
								$writer = new \Zend\Log\Writer\Stream(
										$config['log']['file']);
								$logger->addWriter($writer);
							}
							return $logger;
						},
						'BodyClass' => function  ($sm)
						{
							return new BodyClasses();
						}
				)
		);
	}

	public function getViewHelperConfig ()
	{
		return array(
				'invokables' => array(
						'form' => 'Application\Form\View\Helper\Form',
						'formRow' => 'Application\Form\View\Helper\FormRow',
						'tableCollapse' => 'Application\View\Helper\TableCollapse'
				),
				'formElement' => 'Application\Form\View\Helper\Factory\FormElementFactory',
				'factories' => array(
						'flashMessenger' => function  ($sm)
						{
							$flash = $sm->getServiceLocator()
								->get('ControllerPluginManager')
								->get('flashmessenger');
							$app = $sm->getServiceLocator()->get('Application');
							$messages = new FlashMessenger($app->getRequest(), 
									$app->getMvcEvent());
							$messages->setFlashMessenger($flash);
							
							return $messages;
						}
				)
		);
	}

	public function getFormElementConfig ()
	{
		return array(
				'initializers' => array(
						'ObjectManagerInitializer' => function  ($element, 
								$formElements)
						{
							if ($element instanceof ObjectManagerAwareInterface) {
								$services = $formElements->getServiceLocator();
								$entityManager = $services->get(
										'Doctrine\ORM\EntityManager');
								
								$element->setObjectManager($entityManager);
							}
						}
				)
		);
	}

	public function getControllerPluginConfig ()
	{
		return array(
				'factories' => array(
						'getJsonErrorResponse' => 'Application\Controller\Factory\JsonErrorResponseFactory',
						'getErrorResponse' => 'Application\Controller\Factory\ErrorResponseFactory',
						'logConsole' => function  ($sm)
						{
							$serviceLocator = $sm->getServiceLocator();
							$dataDump = new DataDump($serviceLocator, 
									'logConsole');
							return $dataDump;
						},
						'dumpConsole' => function  ($sm)
						{
							$serviceLocator = $sm->getServiceLocator();
							$dataDump = new DataDump($serviceLocator, 
									'dumpConsole');
							return $dataDump;
						},
						'preDump' => function  ($sm)
						{
							$serviceLocator = $sm->getServiceLocator();
							$dataDump = new DataDump($serviceLocator, 'preDump');
							return $dataDump;
						},
						'varDump' => function  ($sm)
						{
							$serviceLocator = $sm->getServiceLocator();
							$dataDump = new DataDump($serviceLocator, 'varDump');
							return $dataDump;
						}
				)
		);
	}
}
