<?php

namespace Agent\Provider;

use Agent\Entity\Relationship;

/**
 *
 * @author arstropica
 *        
 */
trait RelationshipAwareProvider {

	/**
	 *
	 * @return Relationship $relationship
	 */
	public function getRelationship()
	{
		return $this->relationship;
	}

	/**
	 *
	 * @param \Agent\Entity\Relationship $relationship        	
	 *
	 */
	public function setRelationship(Relationship $relationship)
	{
		$this->relationship = $relationship;
		return $this;
	}

}

?>