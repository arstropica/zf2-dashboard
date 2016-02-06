<?php

namespace Application\Provider;

use Elastica\Client;
use Elastica\Query;
use Elastica\Search;
use Zend\ServiceManager\ServiceLocatorInterface;
use Elastica\Index;

/**
 * Provides Elastic Client Implementation
 *
 * @author arstropica
 *        
 */
trait ElasticaAwareTrait {
	/**
	 *
	 * @var Client
	 */
	protected $client = null;

	/**
	 * Get Elastica Client
	 *
	 * @return Client
	 */
	public function getElasticaClient()
	{
		if (!$this->client) {
			$client = $this->getServiceLocator()
				->get('elastica-client');
			$this->client = $client;
		}
		return $this->client;
	}

	/**
	 * Set Elasta Client
	 *
	 * @param Client $client        	
	 */
	public function setElasticaClient(Client $client)
	{
		$this->client = $client;
		return $this;
	}

	/**
	 * Get Query Count
	 *
	 * @param string $index        	
	 * @param string $type        	
	 *
	 * @return number
	 */
	public function getElasticaCount($index = false, $type = false)
	{
		$elastica_query = new Query();
		$elastica_client = $this->getElasticaClient();
		
		// If you want to restrict your search to a particular index then get
		// that
		$elastica_index = $index ? $elastica_client->getIndex($index) : false;
		
		// If you want to restrict your search to a particular type then get
		// that
		$elastica_type = ($elastica_index && $type) ? $elastica_index->getType($type) : false;
		
		$elastica_search = new Search($elastica_client);
		if ($elastica_index) {
			$elastica_search->addIndex($elastica_index);
		}
		if ($elastica_type) {
			$elastica_search->addType($elastica_type);
		}
		return $elastica_search->count($elastica_query);
	}

	/**
	 * Get Entity Count from Doctrine Query
	 *
	 * @param string $entityNS        	
	 *
	 * @return int
	 */
	public function getEntityCount($entityNS)
	{
		/* @var $em \Doctrine\ORM\EntityManager */
		$em = $this->getServiceLocator()
			->get('doctrine.entitymanager.orm_default');
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $em->createQueryBuilder();
		$qb->add('select', 'COUNT(e) AS vcount')
			->add('from', $entityNS . ' e');
		
		return $qb->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Get Elastica Index
	 *
	 * @param string $index        	
	 *
	 * @return Index
	 */
	public function getElasticaIndex($index)
	{
		$elastica_client = $this->getElasticaClient();
		return $elastica_client->getIndex($index);
	}

	/**
	 * Get Statistics
	 *
	 * @param string $index        	
	 *
	 * @return array
	 */
	public function getElasticaIndexStats($index)
	{
		return $this->getElasticaIndex($index)
			->getStats()
			->getData();
	}

	/**
	 * Delete Index(es)
	 *
	 * @param string|array|bool $index        	
	 * @return bool
	 */
	public function deleteElasticaIndex($index = false)
	{
		$outcome = true;
		$serviceLocator = $this->getServiceLocator();
		/* @var $sm \Doctrine\Search\SearchManager */
		$sm = $serviceLocator->get('doctrine-searchmanager');
		$client = $sm->getClient();
		if (is_array($index)) {
			// Delete indexes
			foreach ( $index as $_index ) {
				if ($client->getIndex($_index)
					->exists()) {
					$client->deleteIndex($_index);
				} else {
					$outcome = false;
				}
			}
		} elseif ($index) {
			if ($client->getIndex($index)
				->exists()) {
				$client->deleteIndex($index);
			} else {
				$outcome = false;
			}
		} else {
			$metadatas = $sm->getMetadataFactory()
				->getAllMetadata();
			
			// Delete indexes
			foreach ( $metadatas as $metadata ) {
				if ($client->getIndex($metadata->index)
					->exists()) {
					$client->deleteIndex($metadata->index);
				} else {
					$outcome = false;
				}
			}
		}
		return $outcome;
	}

	/**
	 * Build Index(es)
	 *
	 * @param string|array|bool $index        	
	 * @param array $config        	
	 *
	 * @return bool
	 */
	public function buildElasticaIndex($index = false, $config = array())
	{
		$outcome = false;
		$serviceLocator = $this->getServiceLocator();
		/* @var $sm \Doctrine\Search\SearchManager */
		$sm = $serviceLocator->get('doctrine-searchmanager');
		$client = $sm->getClient();
		$metadatas = $sm->getMetadataFactory()
			->getAllMetadata();
		
		foreach ( $metadatas as $metadata ) {
			if (is_array($index)) {
				if (in_array($metadata->index, $index)) {
					if (!$client->getIndex($metadata->index)
						->exists()) {
						$client->createIndex($metadata->index, $config);
						$outcome = true;
					}
					$client->createType($metadata);
					unset($index [array_search($metadata->index, $index)]);
				}
			} elseif ($index) {
				if ($metadata->index == $index) {
					if (!$client->getIndex($metadata->index)
						->exists()) {
						$client->createIndex($metadata->index);
						$outcome = true;
					}
					$client->createType($metadata);
				}
			} else {
				if (!$client->getIndex($metadata->index)
					->exists()) {
					$client->createIndex($metadata->index);
					$outcome = true;
				}
				$client->createType($metadata);
			}
		}
		if (is_array($index) && $index) {
			foreach ( $index as $idx ) {
				if (!$client->getIndex($idx)
					->exists()) {
					$client->createIndex($idx, $config);
					$outcome = true;
				}
			}
		}
		return $outcome;
	}

	/**
	 * Get Entity NS from type
	 *
	 * @param string $index        	
	 * @param string $type        	
	 * @return string
	 */
	public function getTypeNS($index, $type)
	{
		$config = $this->getServiceLocator()
			->get('Config');
		$types = isset($config ['elastica'] ['indices'] [$index] ['types']) ? $config ['elastica'] ['indices'] [$index] ['types'] : [ ];
		return isset($types [$type] ['ns']) ? $types [$type] ['ns'] : false;
	}

	/**
	 * Get ServiceLocator
	 *
	 * @return ServiceLocatorInterface
	 */
	abstract public function getServiceLocator();

}

?>