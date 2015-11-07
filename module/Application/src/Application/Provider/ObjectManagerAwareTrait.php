<?php
namespace Application\Provider;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @author arstropica
 *        
 */
trait ObjectManagerAwareTrait
{

	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;

	/**
	 * Set the object manager
	 *
	 * @param ObjectManager $objectManager        	
	 */
	public function setObjectManager (ObjectManager $objectManager)
	{
		$this->objectManager = $objectManager;
	}

	/**
	 * Get the object manager
	 *
	 * @return ObjectManager
	 */
	public function getObjectManager ()
	{
		$objectManager = isset($this->objectManager) ? $this->objectManager : null;
		if (null === $objectManager) {
			$objectManager = $this->getServiceLocator()->get(
					'Doctrine\ORM\EntityManager');
		}
		if ($objectManager) {
			if (! $objectManager->isOpen()) {
				$em = $objectManager->create($objectManager->getConnection(), 
						$objectManager->getConfiguration());
			}
			$this->setObjectManager($objectManager);
		}
		return $this->objectManager;
	}

	abstract public function getServiceLocator ();

	abstract public function setServiceLocator (
			ServiceLocatorInterface $serviceLocator);
}
