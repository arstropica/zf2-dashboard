<?php
namespace Api\Form\Fieldset;

/**
 *
 * @author arstropica
 *        
 */
use Api\Entity\Api;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class ApiFieldset extends Fieldset implements InputFilterProviderInterface
{

	public function __construct (ObjectManager $objectManager, $api_id = 0)
	{
		parent::__construct('api');
		
		$this->setHydrator(new DoctrineHydrator($objectManager))->setObject(
				new Api());
		
		$this->add(
				array(
						'type' => 'Zend\Form\Element\Hidden',
						'name' => 'id',
						'attributes' => array(
								'value' => $api_id
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