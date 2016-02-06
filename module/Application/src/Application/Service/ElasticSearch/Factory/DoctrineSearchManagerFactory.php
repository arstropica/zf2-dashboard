<?php

namespace Application\Service\ElasticSearch\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\Search\SearchManager;
use Doctrine\Common\EventManager;
use Doctrine\Search\Configuration;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Search\Serializer\JMSSerializer;
use JMS\Serializer\SerializationContext;
use Doctrine\Search\ElasticSearch\Client as ElasticSearchClient;
use Elastica\Client as ElasticaClient;
use Application\Provider\FlashMessengerAwareTrait;
use Application\Provider\ServiceLocatorAwareTrait;

/**
 *
 * @author arstropica
 *        
 */
class DoctrineSearchManagerFactory implements FactoryInterface {
	
	use ServiceLocatorAwareTrait, FlashMessengerAwareTrait;

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\ServiceManager\FactoryInterface::createService()
	 *
	 * @return \Doctrine\Search\SearchManager
	 */
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
		$config = $serviceLocator->get('Config');
		$clientConfig = isset($config ['elastica'] ['clients']) ? $config ['elastica'] ['clients'] : [ ];
		$entityConfig = isset($config ['elastica'] ['entities']) ? $config ['elastica'] ['entities'] : [ 
				'paths' => [ ] 
		];
		
		$indicesConfig = isset($config ['elastica'] ['indices']) ? $config ['elastica'] ['indices'] : [ ];
		
		$serializationGroups = isset($config ['elastica'] ['serialization'] ['groups']) ? $config ['elastica'] ['serialization'] ['groups'] : [ ];
		$searchConfig = new Configuration();
		
		$md = $searchConfig->newDefaultAnnotationDriver($entityConfig ['paths']);
		$searchConfig->setMetadataDriverImpl($md);
		$searchConfig->setMetadataCacheImpl(new ArrayCache());
		$serializationContext = SerializationContext::create();
		$serializationContext->enableMaxDepthChecks()
			->setGroups($serializationGroups);
		$searchConfig->setEntitySerializer(new JMSSerializer($serializationContext));
		
		$eventManager = new EventManager();
		$searchManager = new SearchManager($searchConfig, new ElasticSearchClient(new ElasticaClient([ 
				'connections' => $clientConfig 
		])), $eventManager);
		
		try {
			$client = $searchManager->getClient();
			$metadatas = $searchManager->getMetadataFactory()
				->getAllMetadata();
			
			// Create indexes and types
			foreach ( $metadatas as $metadata ) {
				$config = isset($indicesConfig [$metadata->index] ['settings']) ? $indicesConfig [$metadata->index] ['settings'] : [ ];
				if (!$client->getIndex($metadata->index)
					->exists()) {
					$client->createIndex($metadata->index, $config);
				}
				$client->createType($metadata);
			}
		} catch ( \Exception $e ) {
			$this->getFlashMessenger()
				->addErrorMessage($e->getMessage());
			
			/* @var $logger \Zend\Log\Logger */
			$logger = $serviceLocator->get('logger');
			$logger->debug($e->getMessage());
		}
		
		return $searchManager;
	}
}

?>