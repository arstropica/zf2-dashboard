<?php
namespace Lead\Form;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 *
 * @author arstropica
 *        
 */
class EditForm extends Form implements InputFilterProviderInterface
{

	/**
	 *
	 * @var EntityManager
	 */
	protected $objectManager;

	public function __construct (ObjectManager $objectManager)
	{
		parent::__construct();
		
		$this->objectManager = $objectManager;
	}

	public function init ()
	{
		$this->setAttribute('method', 'post');
		$this->add(
				array(
						'type' => 'DoctrineORMModule\Form\Element\EntitySelect',
						'name' => 'account',
						'required' => false,
						'attributes' => array(
								'id' => 'selectAccount',
								'multiple' => false,
								'data-placeholder' => 'Select Account'
						),
						'options' => array(
								'label' => 'Account',
								'target_class' => '\Account\Entity\Account',
								'find_method' => [
										'name' => 'findAll'
								],
								'object_manager' => $this->objectManager,
								'empty_option' => 'Select Account'
						)
				));
	}

	/**
	 *
	 * @return array
	 */
	public function getInputFilterSpecification ()
	{
		return array(
				'account' => [
						'required' => false
				]
		);
	}
}

?>