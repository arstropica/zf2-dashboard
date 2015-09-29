<?php
namespace TenStreet\Entity\PersonalData;

class ContactData
{

	protected $attributes = array();

	protected $InternetEmailAddress;

	protected $PrimaryPhone;

	public function getArrayCopy ()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return the $InternetEmailAddress
	 */
	public function getInternetEmailAddress ()
	{
		return $this->InternetEmailAddress;
	}

	/**
	 *
	 * @return the $PrimaryPhone
	 */
	public function getPrimaryPhone ()
	{
		return $this->PrimaryPhone;
	}

	/**
	 *
	 * @param field_type $InternetEmailAddress        	
	 */
	public function setInternetEmailAddress ($InternetEmailAddress)
	{
		$this->InternetEmailAddress = $InternetEmailAddress;
		return $this;
	}

	/**
	 *
	 * @param field_type $PrimaryPhone        	
	 */
	public function setPrimaryPhone ($PrimaryPhone)
	{
		$this->PrimaryPhone = $PrimaryPhone;
		return $this;
	}

	/**
	 *
	 * @return the $attributes
	 */
	public function getAttributes ()
	{
		return $this->attributes;
	}

	/**
	 *
	 * @param multitype:string $attributes        	
	 */
	public function setAttributes ($attributes)
	{
		$this->attributes = $attributes;
		return $this;
	}
}