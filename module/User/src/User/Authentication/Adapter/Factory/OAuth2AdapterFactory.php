<?php
namespace User\Authentication\Adapter\Factory;
use User\Authentication\Adapter\OAuth2Adapter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\OAuth2\Controller\Exception;

class OAuth2AdapterFactory implements FactoryInterface
{

	/**
	 * Create service
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return OAuth2Adapter
	 */
	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		$config = $serviceLocator->get('Config');
		
		if (! isset($config['User']['db']) || empty($config['User']['db'])) {
			throw new Exception\RuntimeException(
					'The database configuration [\'User\'][\'db\'] for OAuth2 is missing');
		}
		
		$username = isset($config['User']['db']['username']) ? $config['User']['db']['username'] : null;
		$password = isset($config['User']['db']['password']) ? $config['User']['db']['password'] : null;
		$options = isset($config['User']['db']['options']) ? $config['User']['db']['options'] : [];
		
		$connection = [
				'dsn' => $config['User']['db']['dsn'],
				'username' => $username,
				'password' => $password,
				'options' => $options
		];
		
		$pdo = new \PDO($connection['dsn'], $connection['username'], 
				$connection['password'], $connection['options']);
		
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		
		return new OAuth2Adapter($pdo);
	}
}