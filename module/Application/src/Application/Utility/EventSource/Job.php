<?php

namespace Application\Utility\EventSource;

use \Closure;

/**
 * EventSource Job Wrapper
 *
 * @author arstropica
 *        
 */
class Job {
	/**
	 *
	 * @var Closure
	 */
	public $callback;
	
	/**
	 *
	 * @var int|Closure
	 */
	public $progress;
	
	/**
	 *
	 * @var string|Closure
	 */
	public $message;

	/**
	 * Constructor
	 *
	 * @param int|Closure $progress        	
	 * @param string|Closure|array $message        	
	 * @param Closure $callback        	
	 */
	public function __construct($progress, $message, $callback = null)
	{
		$this->callback = $callback;
		$this->progress = $progress;
		$this->message = $message ?  : null;
	}

	/**
	 * Megic Overload Method
	 *
	 * @param string $method        	
	 * @param array $args        	
	 *
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		if (is_callable(array (
				$this,
				$method 
		))) {
			return call_user_func_array($this->$method, $args);
		}
		// else throw exception
	}

}

?>