<?php
namespace Application\Form\View\Helper;
use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormText;

class FormDateRange extends FormText
{

	/**
	 * Attributes valid for the daterange input
	 *
	 * @var array
	 */
	protected static $outputFormat = <<<HTPL
	<div id="%s" class="pullright form-group-daterange">
			<i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
			<span class="form-control-daterange">%s</span> <b class="caret"></b>
	</div>
HTPL;

	protected static $inlineSriptFormat = <<<JTPL
	\$(function() {
		var \$inputId = '#%s';
		function resize (e) {
				var l = \$(\$inputId).val().length;
				\$(\$inputId).width(((l + 1) * 7) + 'px');
		}
		\$(\$inputId).on('change', resize);
		\$(window).on('resize', resize);
	    function cb(start, end) {
			start = start || null;
			end = end || null;
			if (start && end) {
	        	\$(\$inputId).val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
			}
			\$(\$inputId).trigger('change');			
	    }
		if (! \$(\$inputId).val()) {
			cb(moment().subtract(29, 'days'), moment());
		} else {
			cb();
		}
	    \$(\$inputId + '-container').daterangepicker({
			ranges: {
	           'Today': [moment(), moment()],
	           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
	           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
	           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
	           'This Month': [moment().startOf('month'), moment().endOf('month')],
	           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	        }
	    }, cb);
	
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
	public function __invoke (ElementInterface $element = null)
	{
		if (! $element) {
			return $this;
		}
		
		$this->addScripts($element);
		
		$this->addStyles($element);
		
		return $this->render($element);
	}

	public function render (ElementInterface $oElement)
	{
		$id = $oElement->getAttribute('id');
		return sprintf(self::$outputFormat, $id . '-container', 
				parent::render($oElement));
	}

	public function addScripts (ElementInterface $oElement)
	{
		$view = $this->getView();
		
		$id = $oElement->getAttribute('id');
		
		$inlinejs = sprintf(self::$inlineSriptFormat, $id);
		
		$script = $view->inlineScript();
		
		$headScript = $view->headScript();
		
		$script->appendScript($inlinejs, 'text/javascript', 
				array(
						'noescape' => true
				)); // Disable CDATA comments
		
		$headScript->appendFile($view->basePath('js/moment.min.js'));
		
		$headScript->appendFile($view->basePath('js/daterangepicker.js'));
	}

	public function addStyles (ElementInterface $oElement)
	{
		$view = $this->getView();
		
		$style = $view->headLink();
		
		$style->appendStylesheet($view->basePath('css/daterangepicker.css'));
	}
}
