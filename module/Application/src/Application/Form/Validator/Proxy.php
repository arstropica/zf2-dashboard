<?php

namespace Application\Form\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Conditionaly verify an input
 *
 * @author Kristof Mariën <kristof.marien@litus.cc>
 */
class Proxy extends AbstractValidator {
	/**
	 *
	 * @var AbstractValidator
	 */
	private $_validator;
	
	/**
	 *
	 * @var mixed
	 */
	private $_condition;

	public function __construct(AbstractValidator $validator, $condition)
	{
		parent::__construct();
		
		$this->_validator = $validator;
		$this->_condition = $condition;
	}

	/**
	 * Returns array of validation failure messages
	 *
	 * @return array
	 */
	public function getMessages()
	{
		return $this->_validator->getMessages();
	}

	public function isValid($value)
	{
		$this->setValue($value);
		
		if (is_callable($this->_condition)) {
			$result = call_user_func($this->_condition);
		} else {
			$result = (bool) $this->_condition;
		}
		
		if ($result) {
			$valid = $this->_validator->isValid($value);
			
			return $valid;
		}
		
		return true;
	}
}
?>