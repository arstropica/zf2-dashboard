<?php

namespace WebWorks\Mapper;

use Zend\ServiceManager\ServiceManager;
use Zend\Db\Adapter\Adapter;
use WebWorks\Entity\WebWorksData;
use WebWorks\Hydrator\WebWorksHydrator;
use WebWorks\Entity\ApplicationData\DisplayFields\DisplayField;
use WebWorks\Entity\PersonalData\PersonalData;
use WebWorks\Entity\PersonalData\PersonName;
use WebWorks\Entity\PersonalData\PostalAddress;
use WebWorks\Entity\ApplicationData\ApplicationData;
use WebWorks\Entity\PersonalData\ContactData;
use LosBase\Entity\EntityManagerAwareTrait;
use Lead\Entity\Lead;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use WebWorks\Utility\Utility;
use Application\Utility\Helper;
use WebWorks\Entity\ApplicationData\Licenses\License;

class WebWorksDataMapper implements ServiceLocatorAwareInterface {
	
	use EntityManagerAwareTrait;

	protected $dbAdapter;

	protected $sm;

	protected $objectRepository;

	public function __construct(ServiceManager $sm, Adapter $dbAdapter)
	{
		$this->setServiceLocator($sm);
		
		$this->dbAdapter = $dbAdapter;
		
		$this->objRepository = $this->getEntityManager()
			->getRepository("Lead\\Entity\\Lead");
	}

	public function getById($id)
	{
		$webWorksData = false;
		
		$lead = $this->objRepository->find($id);
		
		$options = $this->isValid($lead);
		
		if ($options) {
			
			$webWorksData = $this->getWebWorksData($lead, $options);
			
			if ($webWorksData) {
				
				$ApplicationData = $this->getApplicationData($lead, $options);
				
				$PersonalData = $this->getPersonalData($lead);
				
				$webWorksData->setApplicationData($ApplicationData);
				
				$webWorksData->setPersonalData($PersonalData);
			}
		}
		
		return $webWorksData;
	}

	public function extract($object)
	{
		$hydrator = new WebWorksHydrator(false);
		
		return $hydrator->extract($object);
	}

	protected function getWebWorksData(Lead $lead, $options = array())
	{
		if (!$options) {
			$options = $this->isValid($lead);
			
			if (!$options) {
				return false;
			}
		}
		
		$webWorksEntity = new WebWorksData();
		
		$Source = $options['Source'];
		
		$CompanyId = $options['CompanyId'];
		
		$CompanyName = isset($options['CompanyName']) ? $options['CompanyName'] : $lead->getAccount()
			->getName();
		
		// $DriverId = $lead->getLead()->getDriverid();
		
		$webWorksEntity->setCompanyId($CompanyId);
		
		$webWorksEntity->setSource($Source);
		
		$webWorksEntity->setCompanyName($CompanyName);
		
		// $webWorksEntity->setDriverId($DriverId);
		
		return $webWorksEntity;
	}

	protected function getLicenses(Lead $lead)
	{
		$leadAttributeValue = $lead->findAttribute('cdl', true);
		
		$licenses = array ();
		
		if ($leadAttributeValue) {
			$hasCDL = preg_match('/y|yes/i', $leadAttributeValue->getValue());
			$License = new License();
			if ($hasCDL) {
				$License->setCommercialDriversLicense('y');
				$License->setCurrentLicense('y');
				$License->setLicenseClass('Class A');
			} else {
				$License->setCommercialDriversLicense('n');
			}
			$licenses[] = $License;
		}
		
		return $licenses;
	}

	protected function getDisplayFields(Lead $lead)
	{
		$leadAttributeValues = $lead->findAttributes('Question');
		
		$displayFields = array ();
		
		if ($leadAttributeValues->count() > 0) {
			foreach ( $leadAttributeValues as $attribute ) {
				$DisplayPrompt = $attribute->getAttribute()
					->getAttributeDesc();
				switch (strtolower($DisplayPrompt)) {
					case 'notes' :
						break;
					default :
						$displayField = new DisplayField();
						$DisplayValue = $attribute->getValue();
						$displayField->setDisplayPrompt($DisplayPrompt);
						$displayField->setDisplayValue($DisplayValue);
						$displayFields[] = $displayField;
						break;
				}
			}
		}
		return $displayFields;
	}

	protected function getApplicationData(Lead $lead, $options = [])
	{
		$applicationData = new ApplicationData();
		
		$AppReferrer = isset($options['AppReferrer']) ? $options['AppReferrer'] : false;
		
		$Licenses = $this->getLicenses($lead);
		
		$DisplayFields = $this->getDisplayFields($lead);
		
		if (!empty($AppReferrer)) {
			$applicationData->setAppReferrer($AppReferrer);
		}
		
		$applicationData->setLicenses($Licenses);
		
		$applicationData->setDisplayFields($DisplayFields);
		
		return $applicationData;
	}

	protected function getPersonName(Lead $lead)
	{
		$personName = new PersonName();
		
		$FirstName = $LastName = "";
		
		foreach ( [ 
				'LastName',
				'FirstName' 
		] as $key ) {
			${$key} = $this->getLeadAttributeValue($lead, $key);
		}
		
		$personName->setFamilyName($LastName);
		
		$personName->setGivenName($FirstName);
		
		return $personName;
	}

	protected function getPostalAddress(Lead $lead)
	{
		$postalAddress = new PostalAddress();
		
		$Address = $City = $State = $Zip = "";
		
		$CountryCode = "US";
		
		foreach ( [ 
				'City',
				'State',
				'Address',
				'Zip' 
		] as $key ) {
			switch ($key) {
				case 'State' :
					$state = $this->getLeadAttributeValue($lead, $key);
					${$key} = Utility::getState($state, "short", $state);
					break;
				case 'City' :
					${$key} = $this->getLeadAttributeValue($lead, $key);
					break;
				case 'Address' :
					${$key} = $this->getLeadAttributeValue($lead, $key, "", true, true);
					break;
				default :
					${$key} = $this->getLeadAttributeValue($lead, $key, "", true);
					break;
			}
		}
		
		$postalAddress->setAddress1($Address);
		
		$postalAddress->setMunicipality($City);
		
		$postalAddress->setRegion($State);
		
		$postalAddress->setPostalCode($Zip);
		
		$postalAddress->setCountryCode($CountryCode);
		
		return $postalAddress;
	}

	protected function getContactData(Lead $lead)
	{
		$contactData = new ContactData();
		
		$Email = $Phone = "";
		
		foreach ( [ 
				'Email',
				'Phone' 
		] as $key ) {
			${$key} = $this->getLeadAttributeValue($lead, $key);
		}
		
		$contactData->setInternetEmailAddress($Email);
		
		$contactData->setPrimaryPhone($Phone);
		
		return $contactData;
	}

	protected function getDateOfBirth(Lead $lead)
	{
		$DateOfBirth = null;
		$match = $lead->findAttribute('birth', true);
		if ($match) {
			$date = $match->getValue();
			$DateOfBirth = ($date instanceof \DateTime) ? date_format('m/d/Y', $date) : (Helper::validateDate($date) ? date('m/d/Y', strtotime($date)) : null);
		}
		return $DateOfBirth;
	}

	protected function getPersonalData(Lead $lead)
	{
		$personalData = new PersonalData();
		
		$PersonName = $this->getPersonName($lead);
		
		$PostalAddress = $this->getPostalAddress($lead);
		
		$ContactData = $this->getContactData($lead);
		
		$DateOfBirth = $this->getDateOfBirth($lead);
		
		$personalData->setPersonName($PersonName);
		
		$personalData->setPostalAddress($PostalAddress);
		
		$personalData->setContactData($ContactData);
		
		$personalData->setDateOfBirth($DateOfBirth);
		
		return $personalData;
	}

	protected function isValid(Lead $lead)
	{
		$valid = true;
		$webworks = false;
		$options = false;
		$account = $lead->getAccount();
		
		$apis = $account->getApis();
		// Check WebWorks API
		if ($apis) {
			foreach ( $apis as $api ) {
				if ($api->getName() == 'WebWorks') {
					$webworks = $api;
				}
			}
		}
		
		if ($webworks) {
			$options = [ ];
			$apiOptions = $webworks->getOptions();
			$values = array_map(function ($option) {
				return [ 
						$option->getOption() => $option->getValue() 
				];
			}, array_filter($apiOptions, function ($option) {
				return $option->getApi()
					->getName() == 'WebWorks' && !empty($option->getValue()) && $option->getScope() == 'global';
			}));
			
			foreach ( $values as $option ) {
				foreach ( $option as $key => $value ) {
					switch ($key) {
						case 'AppReferrer' :
							if (!preg_match('/none/i', trim($value))) {
								$options[$key] = preg_replace('/\{lead\}/i', "Lead #" . $lead->getId(), $value);
							}
							break;
						default :
							$options[$key] = $value;
							break;
					}
				}
			}
			
			foreach ( $account->getApiSettings() as $apiSetting ) {
				$option = $apiSetting->getApiOption();
				switch ($option->getOption()) {
					case 'CompanyId' :
						$options['CompanyId'] = $apiSetting->getApiValue();
						break;
					case 'CompanyName' :
						$options['CompanyName'] = $apiSetting->getApiValue();
						break;
					case 'AppReferrer' :
						if (!preg_match('/none/i', trim($apiSetting->getApiValue()))) {
							$options['AppReferrer'] = $apiSetting->getApiValue();
						} else {
							unset($options['AppReferrer']);
						}
						break;
				}
			}
			foreach ( [ 
					'Source',
					'CompanyId' 
			] as $option ) {
				if (!in_array($option, array_keys($options))) {
					$valid = false;
				}
			}
		} else {
			$valid = false;
		}
		
		return $valid ? $options : false;
	}

	protected function getLeadAttributeValue(Lead $lead, $key, $default = "", $desc = false, $exact = false)
	{
		$attribute = $lead->findAttribute($key, $desc, $exact);
		return ($attribute) ? $attribute->getValue() : $default;
	}

	public function setServiceLocator(ServiceLocatorInterface $sm)
	{
		$this->sm = $sm;
		
		return $this;
	}

	public function getServiceLocator()
	{
		return $this->sm;
	}
}