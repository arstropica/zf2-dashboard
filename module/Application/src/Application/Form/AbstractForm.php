<?php
namespace Application\Form;

/**
 *
 * @author arstropica
 *        
 */
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\Form\Form as ZendForm;

abstract class AbstractForm extends ZendForm implements 
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