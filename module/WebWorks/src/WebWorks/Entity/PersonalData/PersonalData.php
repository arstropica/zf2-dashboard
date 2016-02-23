<?php

namespace WebWorks\Entity\PersonalData;

class PersonalData {
	
	protected $PersonName;
	
	protected $PostalAddress;
	
	protected $ContactData;

	protected $DateOfBirth;

	public function getArrayCopy()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return \WebWorks\Entity\PersonalData\PersonName
	 */
	public function getPersonName()
	{
		return $this->PersonName;
	}

	/**
	 *
	 * @return \WebWorks\Entity\PersonalData\PostalAddress
	 */
	public function getPostalAddress()
	{
		return $this->PostalAddress;
	}

	/**
	 *
	 * @param \WebWorks\Entity\PersonalData\PersonName $PersonName        	
	 */
	public function setPersonName($PersonName)
	{
		$this->PersonName = $PersonName;
		return $this;
	}

	/**
	 *
	 * @param \WebWorks\Entity\PersonalData\PostalAddress $PostalAddress        	
	 */
	public function setPostalAddress($PostalAddress)
	{
		$this->PostalAddress = $PostalAddress;
		return $this;
	}

	/**
	 *
	 * @return \WebWorks\Entity\PersonalData\ContactData
	 */
	public function getContactData()
	{
		return $this->ContactData;
	}

	/**
	 *
	 * @param \WebWorks\Entity\PersonalData\ContactData $ContactData        	
	 */
	public function setContactData($ContactData)
	{
		$this->ContactData = $ContactData;
		return $this;
	}
	
	/**
	 * @return \DateTime $DateOfBirth
	 */
	public function getDateOfBirth()
	{
		return $this->DateOfBirth;
	}

	/**
	 * @param \DateTime $DateOfBirth
	 * 
	 * @return PersonalData
	 */
	public function setDateOfBirth($DateOfBirth)
	{
		$this->DateOfBirth = $DateOfBirth;
		return $this;
	}

}