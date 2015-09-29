<?php
namespace Account\Form\Api\Fieldset;

/**
 *
 * @author arstropica
 *        
 */
use Account\Entity\Account;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class AccountFieldset extends Fieldset implements InputFilterProviderInterface
{

	public function __construct (ObjectManager $objectManager, $account_id = 0)
	{
		parent::__construct('account');
		
		$this->setHydrator(new DoctrineHydrator($objectManager))->setObject(
				new Account());
		
		$this->add(
				array(
						'type' => 'Zend\Form\Element\Hidden',
						'name' => 'id',
						'attributes' => array(
								'value' => $account_id
						)
				));
	}

	public function getInputFilterSpecification ()
	{
		return array(
				'id' => array(
						'required' => false
				)
		);
	}
}

?>