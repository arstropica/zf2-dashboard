<?php
namespace Application\Options\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Options\ModuleOptions;

class ModuleOptionsFactory implements FactoryInterface
{

	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		$config = $serviceLocator->get('Config');
		$options = $config['form_options'];
		return new ModuleOptions($options);
	}
}