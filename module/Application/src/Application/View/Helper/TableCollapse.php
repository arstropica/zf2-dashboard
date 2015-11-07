<?php
namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;

class TableCollapse extends AbstractHelper
{

	/**
	 *
	 * @return \Application\View\Helper\TableCollapse
	 */
	public function __invoke ($data, $headings = array(), $classes = array())
	{
		$content = <<<HTML
		<table class="table table-condensed table-collapse">
		%1\$s
		<tbody>
		%2\$s
		</tbody>
		</table>
HTML;
		$trows = [];
		$thead = "";
		
		if ($data && is_array($data)) {
			$headings = $headings ?  : array_keys(current($data));
			$thead = $this->getHead($headings);
			foreach ($data as $i => $record) {
				$c = isset($classes[$i]) ? $classes[$i] : false;
				$trows[] = $this->getRow($record, $headings, $i, $c);
			}
		}
		
		return sprintf($content, $thead, implode("\n", $trows));
	}

	protected function getHead ($headings)
	{
		$THs = implode("\n", 
				array_map(
						function  ($h)
						{
							return sprintf('<th>%s</th>', $h);
						}, array_keys($headings)));
		$THead = <<<HTML
		<thead>
			<tr>
				<th>#</th>
				%s
			</tr>
		</thead>
HTML;
		
		return sprintf($THead, $THs);
	}

	protected function getRow ($data, $headings, $index, $class = false)
	{
		$TR = '<tr data-toggle="collapse" data-target="#data%2$s" class="accordion-toggle %5$s">%3$s</tr>' .
				 '<tr class="hiddenRow"><td colspan="%1$d">' .
				 '<div class="accordian-body collapse" id="data%2$s">%4$s</div>' .
				 '</td></tr>';
		$TD = '<td>%s</td>';
		$TDs = sprintf($TD, $index + 1) . implode("\n", 
				array_map(
						function  ($h) use( $data, $TD)
						{
							$v = "N/A";
							if (isset($data[$h])) {
								$v = is_array($data[$h]) ? current($data[$h]) : $data[$h];
							}
							
							return sprintf($TD, $v);
						}, $headings));
		$TABLE = '<table class="table table-condensed table-striped" class="margin: 0px;">' . implode(
				"\n", 
				array_map(
						function  ($d, $k) use( $TD)
						{
							$v = is_array($d) ? current($d) : $d;
							$k = is_array($d) ? key($d) : $k;
							if (is_array($v)) {
								$output = [];
								foreach ($d as $kv) {
									$k = key($kv);
									$v = current($kv);
									$output[] = sprintf(
											'<tr>' . $TD . $TD . '</tr>', $k, $v);
								}
								return implode("\n", $output);
							} else {
								return sprintf('<tr>' . $TD . $TD . '</tr>', $k, 
										$v);
							}
						}, $data, array_keys($data))) . '</table>';
		return sprintf($TR, count($headings) + 1, $index, $TDs, $TABLE, $class);
	}
}