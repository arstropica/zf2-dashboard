<?php
namespace TenStreet\Entity;

class TenStreetData
{

	protected $Mode;

	protected $Source;

	protected $CompanyId;

	protected $Company;

	protected $DriverId;

	protected $Authentication;

	protected $ApplicationData;

	protected $PersonalData;

	public function getArrayCopy ()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return the $Mode
	 */
	public function getMode ()
	{
		return $this->Mode;
	}

	/**
	 *
	 * @return the $Source
	 */
	public function getSource ()
	{
		return $this->Source;
	}

	/**
	 *
	 * @return the $CompanyId
	 */
	public function getCompanyId ()
	{
		return $this->CompanyId;
	}

	/**
	 *
	 * @return the $Company
	 */
	public function getCompany ()
	{
		return $this->Company;
	}

	/**
	 *
	 * @return the $DriverId
	 */
	public function getDriverId ()
	{
		return $this->DriverId;
	}

	/**
	 *
	 * @return \TenStreet\Entity\Authentication
	 */
	public function getAuthentication ()
	{
		return $this->Authentication;
	}

	/**
	 *
	 * @return \TenStreet\Entity\ApplicationData\ApplicationData
	 */
	public function getApplicationData ()
	{
		return $this->ApplicationData;
	}

	/**
	 *
	 * @return \TenStreet\Entity\PersonalData\PersonalData
	 */
	public function getPersonalData ()
	{
		return $this->PersonalData;
	}

	/**
	 *
	 * @param field_type $Mode        	
	 */
	public function setMode ($Mode)
	{
		$this->Mode = $Mode;
		return $this;
	}

	/**
	 *
	 * @param field_type $Source        	
	 */
	public function setSource ($Source)
	{
		$this->Source = $Source;
		return $this;
	}

	/**
	 *
	 * @param field_type $CompanyId        	
	 */
	public function setCompanyId ($CompanyId)
	{
		$this->CompanyId = $CompanyId;
		return $this;
	}

	/**
	 *
	 * @param field_type $Company        	
	 */
	public function setCompany ($Company)
	{
		$this->Company = $Company;
		return $this;
	}

	/**
	 *
	 * @param field_type $DriverId        	
	 */
	public function setDriverId ($DriverId)
	{
		$this->DriverId = $DriverId;
		return $this;
	}

	/**
	 *
	 * @param \TenStreet\Entity\Authentication $Authentication        	
	 */
	public function setAuthentication ($Authentication)
	{
		$this->Authentication = $Authentication;
		return $this;
	}

	/**
	 *
	 * @param \TenStreet\Entity\ApplicationData\ApplicationData $ApplicationData        	
	 */
	public function setApplicationData ($ApplicationData)
	{
		$this->ApplicationData = $ApplicationData;
		return $this;
	}

	/**
	 *
	 * @param \TenStreet\Entity\PersonalData\PersonalData $PersonalData        	
	 */
	public function setPersonalData ($PersonalData)
	{
		$this->PersonalData = $PersonalData;
		return $this;
	}
}