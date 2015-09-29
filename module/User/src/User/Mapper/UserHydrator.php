<?php
namespace User\Mapper;
use ZfcUser\Mapper\UserHydrator as ZfcUserHydrator;
use ZfcUser\Entity\UserInterface as UserEntityInterface;
use ZfcUser\Mapper\Exception;

class UserHydrator extends ZfcUserHydrator
{

	/**
	 * Extract values from an object
	 *
	 * @param object $object        	
	 * @return array
	 * @throws Exception\InvalidArgumentException
	 */
	public function extract ($object)
	{
		if (! $object instanceof UserEntityInterface) {
			throw new Exception\InvalidArgumentException(
					'$object must be an instance of ZfcUser\Entity\UserInterface');
		}
		/* @var $object UserInterface */
		$data = parent::extract($object);
		if ($data['id'] !== null) {
			$data = $this->mapField('id', 'apiKey', $data);
		} else {
			unset($data['id']);
		}
		return $data;
	}

	/**
	 * Hydrate $object with the provided $data.
	 *
	 * @param array $data        	
	 * @param object $object        	
	 * @return UserInterface
	 * @throws Exception\InvalidArgumentException
	 */
	public function hydrate (array $data, $object)
	{
		if (! $object instanceof UserEntityInterface) {
			throw new Exception\InvalidArgumentException(
					'$object must be an instance of ZfcUser\Entity\UserInterface');
		}
		$data = $this->mapField('apiKey', 'id', $data);
		$parent = get_parent_class($this);
		$grandpa = get_parent_class($parent);
		return $grandpa::hydrate($data, $object);
	}
}
