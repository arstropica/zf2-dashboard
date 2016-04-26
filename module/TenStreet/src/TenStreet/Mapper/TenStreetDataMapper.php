<?php

namespace TenStreet\Mapper;

use Zend\ServiceManager\ServiceManager;
use Zend\Db\Adapter\Adapter;
use TenStreet\Entity\TenStreetData;
use TenStreet\Hydrator\TenStreetHydrator;
use TenStreet\Entity\ApplicationData\DisplayFields\DisplayField;
use TenStreet\Entity\PersonalData\PersonalData;
use TenStreet\Entity\PersonalData\PersonName;
use TenStreet\Entity\PersonalData\PostalAddress;
use TenStreet\Entity\ApplicationData\ApplicationData;
use TenStreet\Entity\PersonalData\ContactData;
use TenStreet\Entity\Authentication;
use LosBase\Entity\EntityManagerAwareTrait;
use Lead\Entity\Lead;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TenStreet\Utility\Utility;
use Application\Utility\Helper;
use TenStreet\Entity\ApplicationData\Licenses\License;

class TenStreetDataMapper implements ServiceLocatorAwareInterface {
	
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
		$tenStreetData = false;
		
		$lead = $this->objRepository->find($id);
		
		$options = $this->isValid($lead);
		
		if ($options) {
			
			$tenStreetData = $this->getTenStreetData($lead, $options);
			
			if ($tenStreetData) {
				
				$Authentication = $this->getAuthorization($lead, $options);
				
				$ApplicationData = $this->getApplicationData($lead, $options);
				
				$PersonalData = $this->getPersonalData($lead);
				
				$tenStreetData->setApplicationData($ApplicationData);
				
				$tenStreetData->setAuthentication($Authentication);
				
				$tenStreetData->setPersonalData($PersonalData);
			}
		}
		
		return $tenStreetData;
	}

	public function extract($object)
	{
		$hydrator = new TenStreetHydrator(false);
		
		return $hydrator->extract($object);
	}

	public function getCredentials($service = 'subject_upload')
	{
		$objRepository = $this->getEntityManager()
			->getRepository("Api\\Entity\\Api");
		
		$criteria = [ 
				'name' => 'Tenstreet' 
		];
		
		$api = $objRepository->findOneBy($criteria);
		
		$authEntity = new Authentication();
		
		if ($api) {
			foreach ( [ 
					'ClientId',
					'Password',
					'Service' 
			] as $key ) {
				$option = $api->findOption($key) ?: false;
				${$key} = $option ? $option->getValue() : "";
				$authEntity->{'set' . $key}(${$key});
			}
		}
		
		return $authEntity;
	}

	protected function getAuthorization(Lead $lead, $options = [])
	{
		if (!$options) {
			$options = $this->isValid($lead);
			
			if (!$options) {
				return false;
			}
		}
		
		$authEntity = new Authentication();
		
		foreach ( [ 
				'ClientId',
				'Password',
				'Service' 
		] as $key ) {
			${$key} = isset($options[$key]) ? $options[$key] : "";
			$authEntity->{'set' . $key}(${$key});
		}
		
		return $authEntity;
	}

	protected function getTenStreetData(Lead $lead, $options = array())
	{
		if (!$options) {
			$options = $this->isValid($lead);
			
			if (!$options) {
				return false;
			}
		}
		
		$tenStreetEntity = new TenStreetData();
		
		$Mode = $options['Mode'];
		
		$Source = $options['Source'];
		
		$CompanyId = $options['CompanyId'];
		
		$Company = isset($options['Company']) ? $options['Company'] : $lead->getAccount()
			->getName();
		
		// $DriverId = $lead->getLead()->getDriverid();
		
		$tenStreetEntity->setMode($Mode);
		
		$tenStreetEntity->setCompanyId($CompanyId);
		
		$tenStreetEntity->setSource($Source);
		
		$tenStreetEntity->setCompany($Company);
		
		// $tenStreetEntity->setDriverId($DriverId);
		
		return $tenStreetEntity;
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
				$displayField = new DisplayField();
				$DisplayPrompt = $attribute->getAttribute()
					->getAttributeDesc();
				$DisplayValue = $attribute->getValue();
				$displayField->setDisplayPrompt($DisplayPrompt);
				$displayField->setDisplayValue($DisplayValue);
				$displayFields[] = $displayField;
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
		$tenstreet = false;
		$options = false;
		$account = $lead->getAccount();
		
		$apis = $account->getApis();
		// Check Tenstreet API
		if ($apis) {
			foreach ( $apis as $api ) {
				if ($api->getName() == 'Tenstreet') {
					$tenstreet = $api;
				}
			}
		}
		
		if ($tenstreet) {
			$options = [ ];
			$apiOptions = $tenstreet->getOptions();
			$values = array_map(function ($option) {
				return [ 
						$option->getOption() => $option->getValue() 
				];
			}, array_filter($apiOptions, function ($option) {
				return $option->getApi()
					->getName() == 'Tenstreet' && !empty($option->getValue()) && $option->getScope() == 'global';
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
					case 'Company' :
						$options['Company'] = $apiSetting->getApiValue();
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
					'ClientId',
					'Password',
					'Mode',
					'Service',
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