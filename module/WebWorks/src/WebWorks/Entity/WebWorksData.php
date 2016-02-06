<?php

namespace WebWorks\Entity;

class WebWorksData {
	
	protected $Source;
	
	protected $CompanyId;
	
	protected $CompanyName;
	
	protected $DriverId;
	
	protected $ApplicationData;
	
	protected $PersonalData;

	public function getArrayCopy()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return the $Source
	 */
	public function getSource()
	{
		return $this->Source;
	}

	/**
	 *
	 * @return the $CompanyId
	 */
	public function getCompanyId()
	{
		return $this->CompanyId;
	}

	/**
	 *
	 * @return the $CompanyName
	 */
	public function getCompanyName()
	{
		return $this->CompanyName;
	}

	/**
	 *
	 * @return the $DriverId
	 */
	public function getDriverId()
	{
		return $this->DriverId;
	}

	/**
	 *
	 * @return \WebWorks\Entity\ApplicationData\ApplicationData
	 */
	public function getApplicationData()
	{
		return $this->ApplicationData;
	}

	/**
	 *
	 * @return \WebWorks\Entity\PersonalData\PersonalData
	 */
	public function getPersonalData()
	{
		return $this->PersonalData;
	}

	/**
	 *
	 * @param field_type $Source        	
	 */
	public function setSource($Source)
	{
		$this->Source = $Source;
		return $this;
	}

	/**
	 *
	 * @param field_type $CompanyId        	
	 */
	public function setCompanyId($CompanyId)
	{
		$this->CompanyId = $CompanyId;
		return $this;
	}

	/**
	 *
	 * @param field_type $CompanyName        	
	 */
	public function setCompanyName($CompanyName)
	{
		$this->CompanyName = $CompanyName;
		return $this;
	}

	/**
	 *
	 * @param field_type $DriverId        	
	 */
	public function setDriverId($DriverId)
	{
		$this->DriverId = $DriverId;
		return $this;
	}

	/**
	 *
	 * @param \WebWorks\Entity\ApplicationData\ApplicationData $ApplicationData        	
	 */
	public function setApplicationData($ApplicationData)
	{
		$this->ApplicationData = $ApplicationData;
		return $this;
	}

	/**
	 *
	 * @param \WebWorks\Entity\PersonalData\PersonalData $PersonalData        	
	 */
	public function setPersonalData($PersonalData)
	{
		$this->PersonalData = $PersonalData;
		return $this;
	}
}