<?php

namespace Application\Form\Element;

// We need to extend from the base element
use Zend\Form\Element;

// Set the class name, and make sure we extend from the
// base element
class Slider extends Element {
	protected $attributes = array (
			'type' => 'Slider' 
	);
}
