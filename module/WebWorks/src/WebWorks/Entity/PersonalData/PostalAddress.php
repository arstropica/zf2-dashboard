<?php

namespace WebWorks\Entity\PersonalData;

class PostalAddress {
	
	protected $Address1;
	
	protected $Municipality;
	
	protected $Region;

	protected $PostalCode;
	
	protected $CountryCode;

	public function getArrayCopy()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return the $Address1
	 */
	public function getAddress1()
	{
		return $this->Address1;
	}

	/**
	 *
	 * @param field_type $Address1        	
	 */
	public function setAddress1($Address1)
	{
		$this->Address1 = $Address1;
		return $this;
	}

	/**
	 *
	 * @return the $Municipality
	 */
	public function getMunicipality()
	{
		return $this->Municipality;
	}

	/**
	 *
	 * @return the $Region
	 */
	public function getRegion()
	{
		return $this->Region;
	}

	/**
	 *
	 * @param field_type $Municipality        	
	 */
	public function setMunicipality($Municipality)
	{
		$this->Municipality = $Municipality;
		return $this;
	}

	/**
	 *
	 * @param field_type $Region        	
	 */
	public function setRegion($Region)
	{
		$this->Region = $Region;
		return $this;
	}

	/**
	 *
	 * @return the $PostalCode
	 */
	public function getPostalCode()
	{
		return $this->PostalCode;
	}

	/**
	 *
	 * @param field_type $PostalCode        	
	 */
	public function setPostalCode($PostalCode)
	{
		$this->PostalCode = $PostalCode;
		return $this;
	}

	/**
	 *
	 * @return the $CountryCode
	 */
	public function getCountryCode()
	{
		return $this->CountryCode;
	}

	/**
	 *
	 * @param field_type $CountryCode        	
	 */
	public function setCountryCode($CountryCode)
	{
		$this->CountryCode = $CountryCode;
		return $this;
	}

}