<?php

namespace Application\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormCollection as ZendFormCollection;
use Application\Utility\Helper;

class FieldCollection extends ZendFormCollection {
	
	/**
	 * Attributes valid for the daterange input
	 *
	 * @var array
	 */
	protected static $outputFormat = <<<HTPL
	<div class="form-collection-wrapper %3\$s" style="padding-bottom: 15px; margin-bottom: 15px; border-bottom: 1px solid #ddd;">
		<div class="row">
			<div class="col-xs-12">
				%2\$s
			</div>
			%1\$s
		</div>
	</div>
HTPL;
	
	protected static $addNew = <<<HTPL
	<div class="col-xs-12">
		<button class="btn addnew btn-info pull-right" onclick="return add_%1\$s()">Add New</button>
	</div>
HTPL;
	
	protected static $addScriptFormat = <<<JTPL
		function add_%1\$s() {
	        var currentCount = $('#%2\$s > fieldset').length;
	        var template = \$('#%2\$s > span').data('template');
	        template = template.replace(/__index__/g, currentCount);
	
			var \$removeBtn = \$('<button type="button" class="btn btn-warning btn-circle removeSetting" onclick="return remove_%1\$s(this)"><i class="glyphicon glyphicon-remove"></i></button>');
			var allow_remove = %3\$s;
			if (allow_remove) {
				\$(template).hide().appendTo($('#%2\$s')).prepend(\$removeBtn).fadeIn(1000).trigger('formcollection_add');
			} else {
				\$(template).hide().appendTo($('#%2\$s')).fadeIn(1000).trigger('formcollection_add');
			}
	        return false;
	    }			
JTPL;
	
	protected static $removeScriptFormat = <<<JTPL
		function remove_%1\$s(btn) {
			\$el = \$(btn).closest('fieldset');
			\$el.fadeOut(1000, function(){\$(this).remove();});
	
			\$el.trigger('formcollection_remove');
			return false;
	    }
		\$(function(){
			var removeBtn = '<button type="button" class="btn btn-warning btn-circle removeSetting" onclick="return remove_%1\$s(this)"><i class="glyphicon glyphicon-remove"></i></button>';
			var onFieldset = function(){
				\$(this).prepend(\$(removeBtn));
			};
			$('#%2\$s > fieldset').each(onFieldset);
		});
JTPL;

	/**
	 * Invoke helper as functor
	 *
	 * Proxies to {@link render()}.
	 *
	 * @param ElementInterface|null $element        	
	 * @return string|FieldCollection
	 */
	public function __invoke(ElementInterface $element = null, $wrap = true)
	{
		if (!$element) {
			return $this;
		}
		
		$this->addScripts($element);
		
		$this->addStyles($element);
		
		$this->setShouldWrap($wrap);
		
		return $this->render($element);
	}

	public function render(ElementInterface $oElement)
	{
		$name = Helper::camelCase($oElement->getName());
		$allow_add = $oElement->getOption('allow_add');
		$allow_remove = $oElement->getOption('allow_remove');
		$class = ($allow_add || $allow_remove) ? "addremove" : "";
		$addnew = $allow_add ? sprintf(self::$addNew, $name) : "";
		$markup = $allow_add ? sprintf(self::$outputFormat, $addnew, parent::render($oElement), $class) : parent::render($oElement);
		return $markup;
	}

	public function addScripts(ElementInterface $oElement)
	{
		$view = $this->getView();
		$inlinejs = '';
		$allow_add = $oElement->getOption('allow_add');
		$allow_remove = $oElement->getOption('allow_remove');
		if ($allow_add || $allow_remove) {
			$id = $oElement->getAttribute('id');
			$name = Helper::camelCase($oElement->getName());
			$remove = $allow_remove ? 'true' : 'false';
			
			if ($allow_add) {
				$inlinejs .= sprintf(self::$addScriptFormat, $name, $id, $remove);
			}
			if ($allow_remove) {
				$inlinejs .= sprintf(self::$removeScriptFormat, $name, $id);
			}
			
			$script = $view->inlineScript();
			
			$headScript = $view->headScript();
			
			$script->appendScript($inlinejs, 'text/javascript', array (
					'noescape' => true 
			)); // Disable CDATA comments
		}
	}

	public function addStyles(ElementInterface $oElement)
	{
		$view = $this->getView();
		
		$style = $view->headLink();
		
		// $style->appendStylesheet($view->basePath('...'));
	}

	public function rowClass($elementOrFieldset = null, $wrap = null)
	{
		$allow_add = $elementOrFieldset->getOption('allow_add');
		if ($allow_add === true) {
			return 'class="row"';
		}
	}
}
