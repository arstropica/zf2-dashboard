<?php

namespace Application\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormText;

class FormSlider extends FormText {
	
	/**
	 * Attributes valid for the daterange input
	 *
	 * @var array
	 */
	protected static $outputFormat = <<<HTPL
	<div id="%s" class="form-group-slider">
			<span class="form-control-slider">%s</span>
	</div>
HTPL;
	
	protected static $inlineSriptFormat = <<<JTPL
	\$(function() {
		var \$inputId = '#%s';
	    \$(\$inputId).slider();	
	});
JTPL;

	/**
	 * Invoke helper as functor
	 *
	 * Proxies to {@link render()}.
	 *
	 * @param ElementInterface|null $element        	
	 * @return string|FormInput
	 */
	public function __invoke(ElementInterface $element = null)
	{
		if (!$element) {
			return $this;
		}
		
		$this->addScripts($element);
		
		$this->addStyles($element);
		
		return $this->render($element);
	}

	public function render(ElementInterface $oElement)
	{
		$id = $oElement->getAttribute('id');
		return sprintf(self::$outputFormat, $id . '-container', parent::render($oElement));
	}

	public function addScripts(ElementInterface $oElement)
	{
		$view = $this->getView();
		
		$id = $oElement->getAttribute('id');
		
		$inlinejs = sprintf(self::$inlineSriptFormat, $id);
		
		$script = $view->inlineScript();
		
		$headScript = $view->headScript();
		
		$script->appendScript($inlinejs, 'text/javascript', array (
				'noescape' => true 
		)); // Disable CDATA comments
		
		$headScript->appendFile($view->basePath('js/bootstrap-slider.min.js'));
	}

	public function addStyles(ElementInterface $oElement)
	{
		$view = $this->getView();
		
		$style = $view->headLink();
		
		$style->appendStylesheet($view->basePath('css/bootstrap-slider.min.css'));
	}
}
