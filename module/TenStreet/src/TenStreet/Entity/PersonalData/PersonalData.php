<?php
namespace TenStreet\Entity\PersonalData;

class PersonalData
{

	protected $PersonName;

	protected $PostalAddress;

	protected $ContactData;

	public function getArrayCopy ()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return \TenStreet\Entity\PersonalData\PersonName
	 */
	public function getPersonName ()
	{
		return $this->PersonName;
	}

	/**
	 *
	 * @return \TenStreet\Entity\PersonalData\PostalAddress
	 */
	public function getPostalAddress ()
	{
		return $this->PostalAddress;
	}

	/**
	 *
	 * @param \TenStreet\Entity\PersonalData\PersonName $PersonName        	
	 */
	public function setPersonName ($PersonName)
	{
		$this->PersonName = $PersonName;
		return $this;
	}

	/**
	 *
	 * @param \TenStreet\Entity\PersonalData\PostalAddress $PostalAddress        	
	 */
	public function setPostalAddress ($PostalAddress)
	{
		$this->PostalAddress = $PostalAddress;
		return $this;
	}

	/**
	 *
	 * @return \TenStreet\Entity\PersonalData\ContactData
	 */
	public function getContactData ()
	{
		return $this->ContactData;
	}

	/**
	 *
	 * @param \TenStreet\Entity\PersonalData\ContactData $ContactData        	
	 */
	public function setContactData ($ContactData)
	{
		$this->ContactData = $ContactData;
		return $this;
	}
}