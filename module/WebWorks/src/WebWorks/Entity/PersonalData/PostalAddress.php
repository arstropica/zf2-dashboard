<?php

namespace WebWorks\Entity\PersonalData;

class PostalAddress {
	
	protected $Municipality;
	
	protected $Region;

	public function getArrayCopy()
	{
		$array = get_object_vars($this);
		return $array;
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
}