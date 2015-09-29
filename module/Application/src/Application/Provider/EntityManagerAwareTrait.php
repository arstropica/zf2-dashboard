<?php
namespace Application\Provider;
use Doctrine\ORM\EntityManager;

trait EntityManagerAwareTrait
{

	/**
	 *
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * Set EntityManager
	 *
	 * @param \Doctrine\ORM\EntityManager $em        	
	 */
	public function setEntityManager (EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * Get EntityManager
	 *
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager ()
	{
		$em = isset($this->em) ? $this->em : null;
		if (null === $em) {
			$em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
		}
		if (! $em->isOpen()) {
			$em = $em->create($em->getConnection(), $em->getConfiguration());
		}
		$this->em = $em;
		return $this->em;
	}
}
