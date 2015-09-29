<?php
namespace Application\Event;
use Zend\EventManager\Event;

/**
 *
 * @author arstropica
 *        
 */
class ServiceEvent extends Event
{

	/**
	 *
	 * @var boolean
	 */
	protected $isError;

	/**
	 *
	 * @var mixed
	 */
	protected $result;

	/**
	 *
	 * @var int
	 */
	protected $entityId;

	/**
	 *
	 * @var string
	 */
	protected $entityClass;

	/**
	 *
	 * @var string
	 */
	protected $description;

	/**
	 *
	 * @var string
	 */
	protected $message;

	/**
	 *
	 * @var int
	 */
	protected $outcome;

	/**
	 *
	 * @return the $isError
	 */
	public function getIsError ()
	{
		return $this->isError;
	}

	/**
	 *
	 * @param boolean $isError        	
	 *
	 * @return ServiceEvent
	 */
	public function setIsError ($isError)
	{
		$this->isError = $isError;
		
		return $this;
	}

	/**
	 * Get result
	 *
	 * @return mixed
	 */
	public function getResult ()
	{
		return $this->result;
	}

	/**
	 * Set result
	 *
	 * @param mixed $result        	
	 *
	 * @return ServiceEvent
	 */
	public function setResult ($result)
	{
		$this->setParam('__RESULT__', $result);
		$this->result = $result;
		return $this;
	}

	/**
	 *
	 * @return the $entityId
	 */
	public function getEntityId ()
	{
		return $this->entityId;
	}

	/**
	 *
	 * @param int $entityId        	
	 *
	 * @return ServiceEvent
	 */
	public function setEntityId ($entityId)
	{
		$this->entityId = $entityId;
		
		return $this;
	}

	/**
	 *
	 * @return the $entityClass
	 */
	public function getEntityClass ()
	{
		return $this->entityClass;
	}

	/**
	 *
	 * @param string $entityClass        	
	 *
	 * @return ServiceEvent
	 */
	public function setEntityClass ($entityClass)
	{
		$this->entityClass = $entityClass;
		
		return $this;
	}

	/**
	 *
	 * @return the $description
	 */
	public function getDescription ()
	{
		return $this->description;
	}

	/**
	 *
	 * @param string $description        	
	 *
	 * @return ServiceEvent
	 */
	public function setDescription ($description)
	{
		$this->description = $description;
		
		return $this;
	}

	/**
	 *
	 * @return the $message
	 */
	public function getMessage ()
	{
		return $this->message;
	}

	/**
	 *
	 * @param string $message        	
	 *
	 * @return ServiceEvent
	 */
	public function setMessage ($message)
	{
		$this->message = $message;
		$this->setParam('message', $message);
		
		return $this;
	}

	/**
	 *
	 * @return the $outcome
	 */
	public function getOutcome ()
	{
		return $this->outcome;
	}

	/**
	 *
	 * @param int $outcome        	
	 *
	 * @return ServiceEvent
	 */
	public function setOutcome ($outcome)
	{
		$this->outcome = $outcome;
		
		return $this;
	}
}
