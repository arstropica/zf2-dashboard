<?php

namespace Agent\Entity\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Agent\Entity\Relationship;

/**
 *
 * @author arstropica
 *        
 */
class RelationshipListener {

	/**
	 * @ORM\PostLoad
	 *
	 * @param Relationship $relationship        	
	 * @param LifecycleEventArgs $event        	
	 */
	public function postLoad(Relationship $relationship, LifecycleEventArgs $event)
	{
		$description = $relationship->getDescription();
		$queryClass = "Agent\\Entity\\Relationship\\Query\\{$description}";
		if (class_exists($queryClass)) {
			$query = new $queryClass();
			$relationship->setQuery($query);
		}
	}
}
