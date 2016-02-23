<?php

namespace WebWorks\Entity\ApplicationData\Licenses;

/**
 *
 * @author arstropica
 *        
 */
class License {
	
	protected $CurrentLicense;
	
	protected $CommercialDriversLicense;
	
	protected $LicenseClass;

	public function getArrayCopy()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return the $CurrentLicense
	 */
	public function getCurrentLicense()
	{
		return $this->CurrentLicense;
	}

	/**
	 *
	 * @param field_type $CurrentLicense        	
	 *
	 * @return License
	 */
	public function setCurrentLicense($CurrentLicense)
	{
		$this->CurrentLicense = $CurrentLicense;
		return $this;
	}

	/**
	 *
	 * @return the $CommercialDriversLicense
	 */
	public function getCommercialDriversLicense()
	{
		return $this->CommercialDriversLicense;
	}

	/**
	 *
	 * @param field_type $CommercialDriversLicense        	
	 *
	 * @return License
	 */
	public function setCommercialDriversLicense($CommercialDriversLicense)
	{
		$this->CommercialDriversLicense = $CommercialDriversLicense;
		return $this;
	}

	/**
	 *
	 * @return the $LicenseClass
	 */
	public function getLicenseClass()
	{
		return $this->LicenseClass;
	}

	/**
	 *
	 * @param field_type $LicenseClass        	
	 *
	 * @return License
	 */
	public function setLicenseClass($LicenseClass)
	{
		$this->LicenseClass = $LicenseClass;
		return $this;
	}

}

?>