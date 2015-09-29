<?php
namespace Api\Form\Fieldset;

/**
 *
 * @author arstropica
 *        
 */
use Api\Entity\Api;
use Application\Form\Fieldset\AbstractFieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Api\Entity\ApiOption;
use Api\Form\Fieldset\ApiFieldset;
use Doctrine\Common\Persistence\ObjectManager;

class ApiOptionFieldset extends AbstractFieldset implements 
		InputFilterProviderInterface
{

	/**
	 *
	 * @var ObjectManager
	 */
	protected $objectManager;

	public function __construct ()
	{
		parent::__construct('api_option_fieldset');
	}

	public function init ()
	{
		$this->setHydrator(
				new DoctrineHydrator($this->getObjectManager(), false))
			->setObject(new ApiOption());
		
		$sm = $this->getFormFactory()
			->getFormElementManager()
			->getServiceLocator();
		
		$routematch = $sm->get('Application')
			->getMvcEvent()
			->getRouteMatch();
		
		$api_id = $routematch->getParam('id', 0);
		
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
		
		$apiFieldset = new ApiFieldset($this->getObjectManager(), $api_id);
		$this->add($apiFieldset);
		
		$this->add(
				array(
						'type' => 'Zend\Form\Element\Hidden',
						'name' => 'option',
						'required' => true
				));
		
		$this->add(
				array(
						'type' => 'Zend\Form\Element\Hidden',
						'name' => 'scope',
						'required' => true
				));
		
		$this->add(
				array(
						'type' => 'Zend\Form\Element\Textarea',
						'name' => 'description',
						'required' => true,
						'options' => array(
								'column-size' => 'md-12 col-sm-12',
								'label' => 'Description'
						),
						'attributes' => array(
								'required' => 'required',
								'readonly' => 'readonly',
								'class' => 'treataslabel autoresize'
						)
				));
		
		$this->add(
				array(
						'type' => 'Zend\Form\Element\Text',
						'name' => 'label',
						'required' => true,
						'options' => array(
								'column-size' => 'md-3 col-md-offset-5 col-sm-12 padding-medium',
								'label' => 'Setting'
						),
						'attributes' => array(
								'required' => 'required',
								'readonly' => 'readonly',
								'class' => 'treataslabel'
						)
				));
		
		$this->add(
				array(
						'type' => 'Zend\Form\Element\Text',
						'name' => 'value',
						'required' => true,
						'options' => array(
								'column-size' => 'md-4 col-sm-12 activevalue padding-medium',
								'label' => 'Value'
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
	}
}

?>