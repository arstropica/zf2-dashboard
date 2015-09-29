<?php
namespace Application\Form\Element;

/**
 *
 * @author arstropica
 *        
 */
use Zend\Form\Element\Collection as ZendCollection;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

class Collection extends ZendCollection implements ViewHelperProviderInterface
{

	protected $attributes = array(
			'type' => 'Collection'
	);

	public function getViewHelperConfig ()
	{
		return array(
				'type' => '\Application\Form\View\Helper\FormCollection'
		);
	}
}

?>