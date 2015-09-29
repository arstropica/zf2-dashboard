<?php
namespace Application\Form;
use Zend\Form\Form;
use Application\Form\Element\PagerSelect;

/**
 *
 * @author arstropica
 *        
 */
class PagerForm extends Form
{

	/**
	 *
	 * @var array
	 */
	protected $increments = [];

	public function __construct ($increments = [])
	{
		parent::__construct('pager');
		
		$this->setPager($increments);
		
		$this->setAttribute('METHOD', 'POST');
		$this->setAttribute('class', 'pagerform');
		$this->setAttribute('id', 'pagerform');
		
		$pager = new PagerSelect($this->increments);
		$this->add($pager);
	}

	public function setPager ($increments = [])
	{
		if (! $increments) {
			$increments = [
					10,
					25,
					50,
					100,
					200
			];
		}
		$this->increments = $increments;
		if ($this->has('limit')) {
			$this->get('limit')->setValueOptions(array_combine($increments, $increments));
		}
		
		return $this;
	}
}

?>