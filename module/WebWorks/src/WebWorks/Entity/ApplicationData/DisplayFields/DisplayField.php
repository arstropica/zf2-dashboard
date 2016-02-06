<?php

namespace WebWorks\Entity\ApplicationData\DisplayFields;

class DisplayField {
	
	protected $DisplayPrompt;
	
	protected $DisplayValue;

	public function getArrayCopy()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return the $DisplayPrompt
	 */
	public function getDisplayPrompt()
	{
		return $this->DisplayPrompt;
	}

	/**
	 *
	 * @return the $DisplayValue
	 */
	public function getDisplayValue()
	{
		return $this->DisplayValue;
	}

	/**
	 *
	 * @param field_type $DisplayPrompt        	
	 */
	public function setDisplayPrompt($DisplayPrompt)
	{
		$this->DisplayPrompt = $DisplayPrompt;
		return $this;
	}

	/**
	 *
	 * @param field_type $DisplayValue        	
	 */
	public function setDisplayValue($DisplayValue)
	{
		$this->DisplayValue = $DisplayValue;
		return $this;
	}
}