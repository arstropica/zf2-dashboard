<?php

namespace Agent\Elastica\Query;

use Elastica\Query\AbstractQuery;
use Elastica\Script;

/**
 * ScriptQuery query.
 *
 * @author ArsTropica <aowilliams@arstropica.com>
 *        
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-script-query.html
 */
class ScriptQuery extends AbstractQuery {
	
	/**
	 * Params.
	 *
	 * @var array Params
	 */
	protected $_params = array ();

	/**
	 * Constructs the ScriptQuery query object.
	 *
	 * @param \Elastica\Script|string|array $script        	
	 */
	public function __construct($script = null)
	{
		if ($script) {
			$this->setScript($script);
		}
	}

	/**
	 * Sets script object.
	 *
	 * @param \Elastica\Script|string|array $script        	
	 *
	 * @return $this
	 */
	public function setScript($script)
	{
		return $this->setParam('script', Script::create($script));
	}

	/**
	 * Converts query to array.
	 *
	 * @see \Elastica\Query\AbstractQuery::toArray()
	 *
	 * @return array Query array
	 */
	public function toArray()
	{
		$array = parent::toArray();
		
		if (isset($array ['script'])) {
			$array ['script'] = $array ['script'] ['script'];
		}
		
		return $array;
	}
}

?>