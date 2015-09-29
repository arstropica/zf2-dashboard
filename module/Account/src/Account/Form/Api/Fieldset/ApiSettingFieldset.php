<?php
namespace Account\Form\Api\Fieldset;

/**
 *
 * @author arstropica
 *        
 */
use Account\Entity\Account;
use Application\Form\Fieldset\AbstractFieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Api\Entity\ApiSetting;
use Account\Form\Api\Fieldset\AccountFieldset;
use Doctrine\Common\Persistence\ObjectManager;

class ApiSettingFieldset extends AbstractFieldset implements 
		InputFilterProviderInterface
{

	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;

	public function __construct ()
	{
		parent::__construct('api_setting_fieldset');
	}

	public function init ()
	{
		$this->setHydrator(
				new DoctrineHydrator($this->getObjectManager(), false))
			->setObject(new ApiSetting());
		
		$sm = $this->getFormFactory()
			->getFormElementManager()
			->getServiceLocator();
		
		$routematch = $sm->get('Application')
			->getMvcEvent()
			->getRouteMatch();
		
		$account_id = $routematch->getParam('id', 0);
		
		$this->add(
				array(
						'type' => 'hidden',
						'name' => 'id',
						'required' => true,
						'attributes' => array(
								'id' => 'id'
						),
						'filters' => array(
								array(
										'name' => 'Int'
								)
						),
						'validators' => array(
								array(
										'name' => 'Digits'
								)
						)
				));
		
		$accountFieldset = new AccountFieldset($this->getObjectManager(), 
				$account_id);
		$this->add($accountFieldset);
		
		$this->add(
				array(
						'name' => 'api',
						'type' => 'DoctrineModule\Form\Element\ObjectSelect',
						'required' => true,
						'options' => array(
								'column-size' => 'md-2 col-sm-12',
								'label' => 'API',
								'object_manager' => $this->getObjectManager(),
								'target_class' => 'Api\Entity\Api',
								'property' => 'name',
								'find_method' => array(
										'name' => 'findAll'
								)
						),
						'attributes' => array(
								'required' => 'required'
						)
				));
		
		$this->add(
				array(
						'name' => 'apiOption',
						'type' => 'DoctrineModule\Form\Element\ObjectSelect',
						'required' => true,
						'options' => array(
								'column-size' => 'md-5 col-sm-12',
								'label' => 'Setting Name',
								'empty_option' => 'Choose Setting',
								'object_manager' => $this->getObjectManager(),
								'target_class' => 'Api\Entity\ApiOption',
								'is_method' => true,
								'property' => 'label',
								'find_method' => array(
										'name' => 'findBy',
										'params' => array(
												'criteria' => array(
														'scope' => 'local'
												),
												'orderBy' => array(
														'api' => 'ASC'
												)
										)
								)
						),
						'attributes' => array(
								'required' => 'required'
						)
				));
		$this->add(
				array(
						'type' => 'Zend\Form\Element\Text',
						'name' => 'apiValue',
						'required' => true,
						'options' => array(
								'column-size' => 'md-5 col-sm-12',
								'label' => 'Setting Value'
						),
						'attributes' => array(
								'required' => 'required'
						)
				));
	}

	/**
	 * Should return an array specification compatible with
	 * {@link Zend\InputFilter\Factory::createInputFilter()}.
	 *
	 * @return array \
	 */
	public function getInputFilterSpecification ()
	{
		return [];
		return array(
				'id' => array(
						'required' => true,
						'filters' => array(
								array(
										'name' => 'Int'
								)
						),
						'validators' => array(
								array(
										'name' => 'Digits'
								)
						)
				),
				'apiOption' => array(
						'required' => true,
						'filters' => array(
								array(
										'name' => 'Zend\Filter\StringTrim'
								)
						)
				),
				'apiValue' => array(
						'required' => true,
						'filters' => array(
								array(
										'name' => 'Zend\Filter\StringTrim'
								)
						)
				),
				'api' => array(
						'required' => true
				)
		);
	}
}

?>