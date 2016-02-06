<?php

namespace Application\Service;

use Elastica\Client;

/**
 * For Get/Setting Elastica Client
 *
 * @author arstropica
 *        
 */
interface ElasticaAwareInterface {

	/**
	 *
	 * @return Client
	 */
	public function getElasticaClient();

	/**
	 *
	 * @param Client $client        	
	 */
	public function setElasticaClient(Client $client);

}

?>