<?php
namespace Application\Service;

/**
 *
 * @author arstropica
 *        
 */
class AccountService extends DoctrineEntityService
{

	public function getEntityRepository ()
	{
		if (null === $this->entityRepository) {
			$this->setEntityRepository(
					$this->getEntityManager()
						->getRepository('Account\Entity\Account'));
		}
		return $this->entityRepository;
	}
}
