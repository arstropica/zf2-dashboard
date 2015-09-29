<?php
namespace Application\Form\Element;
use Zend\Form\Element\Select;

class FormSelect extends Select
{

	public function __construct ($name, $values = array(), $opts = array(), $attrs = array())
	{
		$this->setName($name);
		if ($opts)
			$this->setOptions($opts);
		
		if ($attrs)
			$this->setAttributes($attrs);
		
		if ($values)
			$this->setValueOptions($values);
	}
}