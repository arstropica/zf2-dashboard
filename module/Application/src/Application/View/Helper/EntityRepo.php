<?php
namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 *
 * @author arstropica
 *        
 */
class EntityRepo extends AbstractHelper implements ServiceLocatorAwareInterface
{

	/**
	 *
	 * @var EntityManager
	 */
	protected $em;

	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $parentLocator;

	/**
	 *
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	public function __invoke ($entityClass)
	{
		$this->parentLocator = $this->getServiceLocator()->getServiceLocator();
		
		$em = $this->getEntityManager();
		return $em->getRepository($entityClass);
	}

	/**
	 *
	 * @return EntityManager
	 */
	public function getEntityManager ()
	{
		$em = isset($this->em) ? $this->em : null;
		if (null === $em) {
			$em = $this->parentLocator->get('doctrine.entitymanager.orm_default');
		}
		if (! $em->isOpen()) {
			$em = $em->create($em->getConnection(), $em->getConfiguration());
		}
		$this->em = $em;
		return $this->em;
	}

	public function setServiceLocator (ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}

	public function getServiceLocator ()
	{
		return $this->serviceLocator;
	}
}

