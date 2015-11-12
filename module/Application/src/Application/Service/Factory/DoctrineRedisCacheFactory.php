<?php
namespace Application\Service\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\Common\Cache\RedisCache;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @author arstropica
 *        
 */
class DoctrineRedisCacheFactory implements FactoryInterface
{
	use ServiceLocatorAwareTrait;

	/**
	 * Create service
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 *
	 * @return mixed
	 */
	public function createService (ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
		
		try {
			$cache = $this->init();
			if ($cache) {
				return $cache;
			} else {
				throw new \Exception('Cannot connect to Redis Instance');
			}
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Retrieve Redis Cache Instance
	 *
	 * @return RedisCache|boolean
	 */
	public function init ()
	{
		$redis_cache = false;
		$config = $this->getServiceLocator()->get('Config');
		$config = $config['caches']['redis'];
		
		$namespace = $config['adapter']['options']['namespace'];
		$host = $config['adapter']['options']['server']['host'];
		$port = $config['adapter']['options']['server']['port'];
		$ttl = $config['adapter']['options']['ttl'];
		
		$redis = new \Redis();
		
		/**
		 * This is not required, although it will allow to store anything that
		 * can be serialized by PHP in Redis
		 */
		try {
			$conn = $redis->pconnect($host, $port, $ttl);
			$redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
		} catch (\Exception $e) {
			$conn = false;
		}
		
		if ($conn) {
			$redis_cache = new RedisCache();
			$redis_cache->setNamespace($namespace);
			$redis_cache->setRedis($redis);
		}
		return $redis_cache;
	}
}

?>