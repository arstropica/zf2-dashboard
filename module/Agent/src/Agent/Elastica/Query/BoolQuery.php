<?php

namespace Agent\Elastica\Query;

use Elastica\Query\BoolQuery as BaseQuery;

/**
 *
 * Bool query.
 *
 * @author ArsTropica <aowilliams@arstropica.com>
 *        
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
 *      
 */
class BoolQuery extends BaseQuery {

	/**
	 * Add filter to query.
	 *
	 * @param
	 *        	\Elastica\Query\AbstractQuery |array $args Filter
	 *        	
	 * @return $this
	 */
	public function addFilter($args)
	{
		return $this->_addQuery('filter', $args);
	}
}

?>