<?php

namespace User;

use User\Entity\Role;
use User\Entity\User;
use User\Listener\Register as RegisterListener;
use Zend\Mvc\MvcEvent;
use User\View\UnauthorizedStrategy;
use User\Mapper;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\View\Helper\Navigation\AbstractHelper as ZendViewHelperNavigation;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use User\Controller\Plugin\GoogleUser;
use User\Controller\Plugin\GoogleAuth;
use User\Service\GoogleAuth as GoogleAuthService;

class Module implements AutoloaderProviderInterface {

	public function onBootstrap(MvcEvent $e)
	{
		$eventManager = $e->getApplication()
			->getEventManager();
		$eventManager->attach(new RegisterListener());
		
		$sm = $e->getApplication()
			->getServiceManager();
		$config = $sm->get('Config');
		
		// Add ACL information to the Navigation view helper
		$authorize = $sm->get('BjyAuthorizeServiceAuthorize');
		$acl = $authorize->getAcl();
		$role = $authorize->getIdentity();
		ZendViewHelperNavigation::setDefaultAcl($acl);
		ZendViewHelperNavigation::setDefaultRole($role);
		
		$this->initSession($config ['User'] ['session']);
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig()
	{
		return array (
				'Zend\Loader\ClassMapAutoloader' => array (
						__DIR__ . '/autoload_classmap.php' 
				),
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => array (
								// if we're in a namespace deeper than one level
								// we need to fix the \ in the path
								__NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__) 
						) 
				) 
		);
	}

	public function getServiceConfig()
	{
		return array (
				'invokables' => array (
						'User\Authentication\Adapter\Db' => 'User\Authentication\Adapter\Db',
						'User\Authentication\Storage\Db' => 'User\Authentication\Storage\Db' 
				),
				'factories' => array (
						'User\Entity\Role' => function ($sm) {
							return new Role();
						},
						'User\Entity\User' => function ($sm) {
							return new User();
						},
						'User\View\UnauthorizedStrategy' => function ($sm) {
							return new UnauthorizedStrategy();
						},
						'User\Authentication\Adapter\OAuth2Adapter' => 'User\Authentication\Adapter\Factory\OAuth2AdapterFactory',
						'zfcuser_redirect_callback' => 'User\Service\Factory\RedirectCallbackFactory',
						'User_user_mapper' => function ($sm) {
							$options = $sm->get('zfcuser_module_options');
							$mapper = new Mapper\User();
							$mapper->setDbAdapter($sm->get('zfcuser_zend_db_adapter'));
							$entityClass = $options->getUserEntityClass();
							$mapper->setEntityPrototype(new $entityClass());
							$mapper->setHydrator(new Mapper\UserHydrator());
							$mapper->setTableName($options->getTableName());
							return $mapper;
						},
						'GoogleClient' => function ($sm) {
							$config = $sm->get('Config');
							$gapi_settings = isset($config ['User'] ['gapi']) ? $config ['User'] ['gapi'] : false;
							if ($gapi_settings) {
								$client = new \Google_Client();
								$client->setAccessType('offline');
								$client->setApplicationName('Target Media Partners');
								$client->setClientId($gapi_settings ['CLIENT_ID']);
								$client->setClientSecret($gapi_settings ['CLIENT_SECRET']);
								$client->setRedirectUri($gapi_settings ['CALLBACK']);
								$client->setDeveloperKey($gapi_settings ['DEVELOPER_KEY']);
								$client->setScopes(array (
										'profile',
										'email' 
								));
								return $client;
							} else {
								throw new \Exception('Settings for the Google Application were not found.');
							}
						},
						'GoogleAuth' => function ($sm) {
							$serviceLocator = $sm;
							$googleAuth = new GoogleAuthService($serviceLocator);
							return $googleAuth;
						},
						'User\Cache' => function ($sm) {
							$cache = \Zend\Cache\StorageFactory::factory(array (
									'adapter' => 'filesystem',
									'plugins' => array (
											'exception_handler' => array (
													'throw_exceptions' => FALSE 
											),
											'serializer' 
									) 
							));
							
							$cache->setOptions(array (
									'cache_dir' => './data/cache',
									'ttl' => 60 * 60 
							));
							
							return $cache;
						} 
				) 
		);
	}

	public function getControllerPluginConfig()
	{
		return array (
				'factories' => array (
						'GoogleUser' => function ($sm) {
							$serviceLocator = $sm->getServiceLocator();
							$googleUser = new GoogleUser($serviceLocator);
							return $googleUser;
						},
						'isGoogleAuthorized' => function ($sm) {
							$serviceLocator = $sm->getServiceLocator();
							$googleAuth = new GoogleAuth($serviceLocator);
							return $googleAuth;
						} 
				) 
		);
	}

	public function initSession($config)
	{
		$sessionConfig = new SessionConfig();
		$sessionConfig->setOptions($config);
		$sessionManager = new SessionManager($sessionConfig);
		$sessionManager->start();
		Container::setDefaultManager($sessionManager);
	}
}
