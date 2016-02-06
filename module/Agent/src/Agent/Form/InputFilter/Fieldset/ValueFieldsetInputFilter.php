<?php

namespace Agent\Form\InputFilter\Fieldset;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Application\Provider\EntityManagerAwareTrait;
use Application\Form\Validator\Proxy;
use Zend\Validator\NotEmpty;

/**
 *
 * @author arstropica
 *        
 */
class ValueFieldsetInputFilter extends InputFilter implements ServiceLocatorAwareInterface {
	
	use ServiceLocatorAwareTrait, EntityManagerAwareTrait;
	
	protected $fields;

	public function __construct(ServiceLocatorInterface $serviceLocator)
	{
		
		$this->setServiceLocator($serviceLocator);
		
		$entityManager = $this->getServiceLocator()
			->get('Doctrine\ORM\EntityManager');
		
		$this->setEntityManager($entityManager);
		
		$this->init();
	}

	public function init()
	{
		$factory = new InputFactory();
		
		$this->add($factory->createInput(array (
				'name' => 'id',
				'required' => false 
		)));
		
		$this->add($factory->createInput(array (
				'name' => 'type',
				'required' => true,
				'validators' => array (
						array (
								'name' => 'NotEmpty' 
						) 
				) 
		)));
		
		$this->add($factory->createInput(array (
				'name' => 'value',
				'required' => false 
		)));
		
		$this->fields = [ 
				'string',
				'multiple',
				'boolean',
				'location',
				'daterange',
				'range' 
		];
		
		foreach ( $this->fields as $field ) {
			$this->add($factory->createInput(array (
					'name' => $field,
					'required' => true,
					'validators' => $this->_getValidatorSpecification($field) 
			)));
		
		}
	}

	public function isValid()
	{
		$type = $this->getType();
		
		if ($type) {
			$field = $this->_getRelationshipInput($type);
			if ($field) {
				$this->setValidationGroup([ 
						'id',
						'type',
						$field 
				]);
			}
		}
		return parent::isValid();
	}

	/**
	 *
	 * @return string $type
	 */
	public function getType()
	{
		return $this->getValue('type');
	}

	protected function _getRelationshipInput($type)
	{
		$className = 'Agent\Entity\Relationship';
		$objRepository = $this->getEntityManager()
			->getRepository($className);
		$relationship = $objRepository->findOneBy([ 
				'type' => $type 
		]);
		if ($relationship) {
			return $relationship->getInput();
		}
		return false;
	}

	protected function _getValidatorSpecification($field)
	{
		$self = $this;
		
		$notEmptyValidator = new NotEmpty();
		$condition = function () use($self, $field) {
			$type = $self->get('type')
				->getValue();
			if ($type) {
				$input = $self->_getRelationshipInput($type);
				return $field == $input;
			}
			return false;
		};
		
		$proxyValidator = new Proxy($notEmptyValidator, $condition);
		
		return array (
				array (
						'name' => 'Zend\Validator\NotEmpty',
						'options' => array (
								'type' => 'null' 
						) 
				),
				$proxyValidator 
		);
	}
}

?>