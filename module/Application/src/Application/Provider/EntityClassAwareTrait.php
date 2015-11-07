<?php
namespace Application\Provider;

/**
 *
 * @author arstropica
 *        
 */
trait EntityClassAwareTrait
{

	/**
	 *
	 * @var string
	 */
	protected $entityClass;

	/**
	 * Get Entity Class
	 *
	 * @return string
	 */
	public function getEntityClass ()
	{
		return $this->entityClass;
	}

	/**
	 * Set Entity Class
	 *
	 * @param string $entityClass        	
	 */
	public function setEntityClass ($entityClass)
	{
		$this->entityClass = $entityClass;
		
		return $this;
	}
}

?>