<?php
namespace Lead\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Application\Form\Fieldset\AbstractFieldset;

class ImportFieldset extends AbstractFieldset implements 
		InputFilterProviderInterface
{

	protected $_fieldNames;

	protected $isAdmin;

	public function __construct (ObjectManager $objectManager, $fields = array(), 
			$isAdmin = false)
	{
		parent::__construct('form');
		
		$this->isAdmin = $isAdmin;
		
		$this->setObjectManager($objectManager);
		
		foreach ($fields as $field) {
			$attributeFieldset = new AttributeValueFieldset($objectManager, 
					$field, $isAdmin);
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