<?php
namespace Application\Utility;

/**
 *
 * @author arstropica
 *        
 */
class Helper
{

	/**
	 * Recursively implodes an array with optional key inclusion
	 *
	 * Example of $include_keys output: key, value, key, value, key, value
	 *
	 * @access public
	 * @param array $array
	 *        	multi-dimensional array to recursively implode
	 * @param string $glue
	 *        	value that glues elements together
	 * @param bool $include_keys
	 *        	include keys before their values
	 * @param bool $trim_all
	 *        	trim ALL whitespace from string
	 * @return string imploded array
	 */
	public static function recursive_implode (array $array, $glue = ',', 
			$include_keys = false, $trim_all = false)
	{
		$glued_string = '';
		
		// Recursively iterates array and adds key/value to glued string
		array_walk_recursive($array, 
				function  ($value, $key) use( $glue, $include_keys, 
				&$glued_string)
				{
					$include_keys and $glued_string .= $key . $glue;
					$glued_string .= $value . $glue;
				});
		
		// Removes last $glue from string
		strlen($glue) > 0 and
				 $glued_string = substr($glued_string, 0, - strlen($glue));
		
		// Trim ALL whitespace
		$trim_all and
				 $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);
		
		return (string) $glued_string;
	}
}

?>