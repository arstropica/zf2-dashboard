<?php
namespace Application\Form\Fieldset;

/**
 *
 * @author arstropica
 *        
 */
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\Form\Fieldset;

abstract class AbstractFieldset extends Fieldset implements 
		ObjectManagerAwareInterface
{

	protected $objectManager;

	public function setObjectManager (ObjectManager $objectManager)
	{
		$this->objectManager = $objectManager;
	}

	public function getObjectManager ()
	{
		return $this->objectManager;
	}
}

?>