<?php
namespace Application\Options;
use Zend\Stdlib\AbstractOptions;

/**
 * Hold options for LeadFilterForm module
 */
class ModuleOptions extends AbstractOptions
{

	protected $ignoredViewHelpers;

	public function getIgnoredViewHelpers ()
	{
		return $this->ignoredViewHelpers;
	}

	public function setIgnoredViewHelpers ($ignoredViewHelpers)
	{
		$this->ignoredViewHelpers = $ignoredViewHelpers;
	}
}