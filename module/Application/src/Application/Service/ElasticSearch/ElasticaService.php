<?php

namespace Application\Service\ElasticSearch;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Provider\SearchManagerAwareTrait;
use Application\Provider\ElasticaAwareTrait;
use Doctrine\Search\SearchManager;

/**
 * Elastica Ancilliary Service
 *
 * @author arstropica
 *        
 */
class ElasticaService implements ServiceLocatorAwareInterface {
	use ServiceLocatorAwareTrait, SearchManagerAwareTrait, ElasticaAwareTrait;
	
	protected $_config;

	public function __construct(ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
	}

	public function init()
	{
		$config = $this->getServiceLocator()
			->get('Config');
		$this->_config = isset($config ['elastica']) ? $config ['elastica'] : [ ];
		$searchManager = $this->getServiceLocator()
			->get('doctrine-searchmanager');
		$this->setSearchManager($searchManager);
		return $this;
	}

	public function exists()
	{
		$searchManager = $this->getSearchManager();
		return ($searchManager && $searchManager instanceof SearchManager);
	}

	public function setup()
	{
		$elastica_client = $this->getSearchManager()
			->getClient();
		$config = $this->getConfig();
		$indices = isset($config ['indices']) ? $config ['indices'] : [ ];
		foreach ( $indices as $index => $options ) {
			try {
				$settings = isset($options ['settings']) ? $options ['settings'] : [ ];
				$mapping = isset($options ['types']) ? $options ['types'] : [ ];
				/* @var $elastica_index \Elastica\Index */
				$elastica_index = $elastica_client->getIndex($index);
				if (!$elastica_index->exists()) {
					$this->buildElasticaIndex($index, $settings);
					$elastica_index->flush(true);
				}
				if ($mapping) {
					$types = array_keys($mapping);
					$elastica_mapping = $elastica_index->getMapping();
					$elastica_types = array_keys($elastica_mapping);
					if (($untyped = array_diff($types, $elastica_types)) == true) {
						$elastica_client->deleteIndex($index);
						if (!$elastica_client->getIndex($index)
							->exists()) {
							$elastica_index = $elastica_client->createIndex($index, $settings);
							foreach ( $types as $type ) {
								$NS = isset($mapping [$type] ['ns']) ? $mapping [$type] ['ns'] : false;
								try {
									if ($NS) {
										$metadata = $this->getSearchManager()
											->getClassMetadata($NS);
										if ($metadata) {
											$elastica_client->createType($metadata);
										}
									}
								} catch ( \Exception $e ) {
									// Fail Silently
									continue;
								}
							}
						}
						$elastica_index->flush(true);
					}
				}
			} catch ( \Exception $e ) {
				// Fail silently
			}
		}
	}

	protected function getConfig()
	{
		if (!isset($this->_config)) {
			$this->init();
		}
		return $this->_config;
	}

}

?>