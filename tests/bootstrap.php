<?php
namespace ApplicationTest;

use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

class bootstrap
{

    static $serviceManager;

    static function go()
    {
        require_once __DIR__ . '/MockUser.php';
        
        // Make everything relative to the root
        chdir(dirname(__DIR__));
        
        // Setup autoloading
        require_once (__DIR__ . '/../init_autoloader.php');
        
        // Run application
        $config = require ('config/application.config.php');
        \Zend\Mvc\Application::init($config);
        
        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        
        self::$serviceManager = $serviceManager;
        
        self::loadListeners(dirname(__DIR__) . '/module/');
        
    }

    static public function getServiceManager()
    {
        return self::$serviceManager;
    }

    static public function loadListeners($module_path)
    {
        foreach (glob($module_path.'*', GLOB_ONLYDIR) as $module) {
            $moduleName = basename($module);
            $moduleTestCaseClassname = '\\' . $moduleName . 'Test\\Framework\\TestCase';
            $listenerOptions = new \Zend\ModuleManager\Listener\ListenerOptions(array(
                'module_paths' => array($module_path . '/' . $moduleName)
            ));
            $defaultListener = new \Zend\ModuleManager\Listener\DefaultListenerAggregate($listenerOptions);
            if (method_exists($moduleTestCaseClassname, 'setLocator')) {
                $config = $defaultListener->getConfigListener()->getMergedConfig();
                
                $di = new \Zend\Di\Di();
                $di->instanceManager()->addTypePreference('Zend\Di\LocatorInterface', $di);
                
                if (isset($config['di'])) {
                    $diConfig = new \Zend\Di\Config($config['di']);
                    $diConfig->configure($di);
                }
                
                $routerDiConfig = new \Zend\Di\Config(array(
                    'definition' => array(
                        'class' => array(
                            'Zend\Mvc\Router\RouteStackInterface' => array(
                                'instantiator' => array(
                                    'Zend\Mvc\Router\Http\TreeRouteStack',
                                    'factory'
                                )
                            )
                        )
                    )
                ));
                $routerDiConfig->configure($di);
                
                call_user_func_array($moduleTestCaseClassname . '::setLocator', array(
                    $di
                ));
            }
        }
    }
}

bootstrap::go();
