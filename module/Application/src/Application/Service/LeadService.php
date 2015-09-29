<?php
namespace Application\Service;

/**
 *
 * @author arstropica
 *        
 */
class LeadService extends DoctrineEntityService
{

	public function getEntityRepository ()
	{
		if (null === $this->entityRepository) {
			$this->setEntityRepository(
					$this->getEntityManager()
						->getRepository('Lead\Entity\Lead'));
		}
		return $this->entityRepository;
	}
}
