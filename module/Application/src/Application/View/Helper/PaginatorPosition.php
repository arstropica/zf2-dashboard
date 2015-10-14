<?php
namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\Paginator\Paginator;

/**
 *
 * @author arstropica
 *        
 */
class PaginatorPosition extends AbstractHelper
{

	/**
	 *
	 * @return string
	 */
	public function __invoke (Paginator $paginator)
	{
		$content = <<<HTML
		<label class="inline control-label">Displaying: </label>
		<span>
			%1\$s - %2\$s of %3\$s
		</span>
HTML;
		
		$limit = $paginator->getItemCountPerPage();
		$page = $paginator->getCurrentPageNumber();
		$current = $paginator->getCurrentItemCount();
		$total = $paginator->getTotalItemCount();
		$offset =  $limit * $page - $limit;
		return sprintf($content, (intval($offset) + 1), (intval($offset) + intval($current)), $total);
	}
}

?>