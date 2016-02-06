<?php

namespace Agent\Provider;

use Agent\Entity\Relationship;

/**
 *
 * @author arstropica
 *        
 */
interface RelationshipAwareInterface {

	/**
	 *
	 * @return Relationship $relationship
	 */
	public function getRelationship();

	/**
	 *
	 * @param \Agent\Entity\Relationship $relationship        	
	 *
	 */
	public function setRelationship(Relationship $relationship);

}

?>