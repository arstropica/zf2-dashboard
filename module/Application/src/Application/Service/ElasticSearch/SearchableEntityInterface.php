<?php

namespace Application\Service\ElasticSearch;

use Doctrine\Search\SearchManager;

/**
 * Get/Setter for SearchManager
 *
 * @author arstropica
 *        
 */
interface SearchableEntityInterface {

	/**
	 * Get SearchManager
	 *
	 * @return SearchManager
	 */
	public function getSearchManager();

	/**
	 * Set SearchManager
	 *
	 * @param SearchManager $searchManager        	
	 */
	public function setSearchManager(SearchManager $searchManager);
}

?>