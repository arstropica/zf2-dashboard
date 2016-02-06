<?php

namespace Application\Provider;

use Igorw\EventSource\Stream;

/**
 *
 * @author arstropica
 *        
 */
trait EventSourceAwareTrait {

	/**
	 * Get EventSourceStream
	 *
	 * @return Stream
	 */
	public function getStream()
	{
		if (!isset($this->stream)) {
			$stream = new Stream();
			$this->stream = $stream;
		}
		return $this->stream;
	}

	/**
	 * Output Event Stream Headers
	 *
	 * @return void
	 */
	public function outputStreamHeaders()
	{
		foreach ( Stream::getHeaders() as $name => $value ) {
			header("$name: $value");
		}
	}

	/**
	 * Write Event Stream
	 *
	 * @param int $id        	
	 * @param string $event        	
	 * @param mixed $data        	
	 * @param number $retry        	
	 *
	 * @return void
	 */
	public function writeStream($id, $event, $data, $retry = 500)
	{
		$message = is_array($data) ? json_encode($data) : $data;
		$this->getStream()
			->event()
			->setEvent($event)
			->setId($id)
			->setData($message)
			->setRetry($retry)
			->end()
			->flush();
	}

}

?>