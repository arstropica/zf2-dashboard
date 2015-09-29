<?php
namespace Application\Form\Element;
use Zend\Form\Element\Select;
use Application\Form\View\Helper\Form as FormHelper;
use Zend\InputFilter\InputProviderInterface;

class PagerSelect extends Select implements InputProviderInterface
{

	/**
	 *
	 * @var ValidatorInterface
	 */
	protected $validator;

	public function __construct ($values = array())
	{
		$this->setName('limit');
		$this->setOptions(
				[
						'class' => 'pagerSelect',
						'label' => '#/ Page:',
						'label_attributes' => [
								'class' => 'inline col-xs-6'
						],
						'column-size' => 'xs-6',
						'layout' => FormHelper::LAYOUT_HORIZONTAL,
						'allow_empty' => true
				]);
		
		$this->setAttributes([
				'class' => 'pagerSelect',
				'onchange' => 'if (this.value) this.form.submit();',
				'value' => 10
		]);
		
		if ($values) {
			$this->setValueOptions(array_combine($values, $values));
		}
	}

	/**
	 * Provide default input rules for this element
	 *
	 * Attaches a phone number validator.
	 *
	 * @return array
	 */
	public function getInputSpecification ()
	{
		return array(
				'name' => $this->getName(),
				'required' => false,
				'filters' => []
		);
	}
}