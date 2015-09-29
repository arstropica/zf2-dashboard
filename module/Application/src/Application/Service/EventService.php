<?php
namespace Application\Service;

/**
 *
 * @author arstropica
 *        
 */
class EventService extends DoctrineEntityService
{

	public function getEntityRepository ()
	{
		if (null === $this->entityRepository) {
			$this->setEntityRepository(
					$this->getEntityManager()
						->getRepository('Event\Entity\Event'));
		}
		return $this->entityRepository;
	}
}
