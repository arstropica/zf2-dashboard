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

class Module implements AutoloaderProviderInterface
{

	public function onBootstrap (MvcEvent $e)
	{
		$eventManager = $e->getApplication()->getEventManager();
		$eventManager->attach(new RegisterListener());
		
		$sm = $e->getApplication()->getServiceManager();
		$config = $sm->get('Config');
		
		// Add ACL information to the Navigation view helper
		$authorize = $sm->get('BjyAuthorizeServiceAuthorize');
		$acl = $authorize->getAcl();
		$role = $authorize->getIdentity();
		ZendViewHelperNavigation::setDefaultAcl($acl);
		ZendViewHelperNavigation::setDefaultRole($role);
		
		$this->initSession($config['User']['session']);
	}

	public function getConfig ()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig ()
	{
		return array(
				'Zend\Loader\ClassMapAutoloader' => array(
						__DIR__ . '/autoload_classmap.php'
				),
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								// if we're in a namespace deeper than one level
								// we need to fix the \ in the path
								__NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__)
						)
				)
		);
	}

	public function getServiceConfig ()
	{
		return array(
				'invokables' => array(
						'User\Authentication\Adapter\Db' => 'User\Authentication\Adapter\Db',
						'User\Authentication\Storage\Db' => 'User\Authentication\Storage\Db'
				),
				'factories' => array(
						'User\Entity\Role' => function  ($sm)
						{
							return new Role();
						},
						'User\Entity\User' => function  ($sm)
						{
							return new User();
						},
						'User\View\UnauthorizedStrategy' => function  ($sm)
						{
							return new UnauthorizedStrategy();
						},
						'User\Authentication\Adapter\OAuth2Adapter' => 'User\Authentication\Adapter\Factory\OAuth2AdapterFactory',
						'User_user_mapper' => function  ($sm)
						{
							$options = $sm->get('zfcuser_module_options');
							$mapper = new Mapper\User();
							$mapper->setDbAdapter($sm->get('zfcuser_zend_db_adapter'));
							$entityClass = $options->getUserEntityClass();
							$mapper->setEntityPrototype(new $entityClass());
							$mapper->setHydrator(new Mapper\UserHydrator());
							$mapper->setTableName($options->getTableName());
							return $mapper;
						}
				)
		);
	}

	public function initSession ($config)
	{
		$sessionConfig = new SessionConfig();
		$sessionConfig->setOptions($config);
		$sessionManager = new SessionManager($sessionConfig);
		$sessionManager->start();
		Container::setDefaultManager($sessionManager);
	}
}
