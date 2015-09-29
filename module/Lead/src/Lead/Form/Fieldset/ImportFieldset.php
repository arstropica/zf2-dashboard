<?php
namespace Lead\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Application\Form\Fieldset\AbstractFieldset;

class ImportFieldset extends AbstractFieldset implements 
		InputFilterProviderInterface
{

	protected $_fieldNames;

	public function __construct (ObjectManager $objectManager, $fields = array())
	{
		parent::__construct('form');
		
		$this->setObjectManager($objectManager);
		
		foreach ($fields as $field) {
			$attributeFieldset = new AttributeValueFieldset($objectManager, 
					$field);
			$attributeFieldset->setName($field);
			$this->add($attributeFieldset);
		}
	}

	public function getInputFilterSpecification ()
	{
		return [];
	}

	public static function getCommonFieldNames ()
	{
		return array(
				"First Name" => "FirstName",
				"Last Name" => "LastName",
				"City" => "City",
				"State" => "State",
				"Email" => "Email",
				"Phone" => "Phone",
				"IP Address" => "ipaddress",
				"Referrer" => "referrer",
				"Time Created" => "timecreated"
		);
	}
}