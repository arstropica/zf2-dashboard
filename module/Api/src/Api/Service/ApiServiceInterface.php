<?php

namespace Api\Service;

use Zend\View\Model\JsonModel;

/**
 *
 * @author arstropica
 *        
 */
interface ApiServiceInterface {

	/**
	 * Send data to API using entity identifier.
	 *
	 * @param int $id
	 *        	Entity Identifier
	 * @param $service Name
	 *        	of Active API
	 *        	
	 * @return JsonModel|array|boolean
	 */
	public function send($id, $service);

	/**
	 * Log Entity action Event
	 *
	 * @param mixed $event        	
	 *
	 * @return void
	 */
	public function logEvent($event);

	/**
	 * Get API Options for Entity
	 *
	 * @param int $id        	
	 * @param string $service        	
	 *
	 * @return array
	 */
	public function getOptions($id, $service);

	/**
	 * Get data to send.
	 *
	 * @param int $id        	
	 *
	 * @return mixed $data
	 */
	public function getData($id);

	/**
	 * Return response
	 *
	 * @param array $data        	
	 */
	public function respond($data = null);

	/**
	 * Return Error Response
	 *
	 * @param \Exception $e        	
	 *
	 * @return array|JsonModel
	 */
	public function respondError(\Exception $e);

	/**
	 *
	 * @param mixed $result        	
	 *
	 * @return array|JsonModel
	 */
	public function respondSuccess($result);
}
