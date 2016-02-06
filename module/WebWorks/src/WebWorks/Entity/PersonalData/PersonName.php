<?php

namespace WebWorks\Entity\PersonalData;

class PersonName {
	
	protected $GivenName;
	
	protected $FamilyName;

	public function getArrayCopy()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return the $GivenName
	 */
	public function getGivenName()
	{
		return $this->GivenName;
	}

	/**
	 *
	 * @return the $FamilyName
	 */
	public function getFamilyName()
	{
		return $this->FamilyName;
	}

	/**
	 *
	 * @param field_type $GivenName        	
	 */
	public function setGivenName($GivenName)
	{
		$this->GivenName = $GivenName;
		return $this;
	}

	/**
	 *
	 * @param field_type $FamilyName        	
	 */
	public function setFamilyName($FamilyName)
	{
		$this->FamilyName = $FamilyName;
		return $this;
	}
}