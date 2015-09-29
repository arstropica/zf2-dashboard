<?php
namespace Lead\Form\Api\Fieldset;

/**
 *
 * @author arstropica
 *        
 */
use Lead\Entity\Lead;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class LeadFieldset extends Fieldset implements InputFilterProviderInterface
{

	public function __construct (ObjectManager $objectManager, $lead_id = 0)
	{
		parent::__construct('lead');
		
		$this->setHydrator(new DoctrineHydrator($objectManager))->setObject(
				new Lead());
		
		$this->add(
				array(
						'type' => 'Zend\Form\Element\Hidden',
						'name' => 'id',
						'attributes' => array(
								'value' => $lead_id
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