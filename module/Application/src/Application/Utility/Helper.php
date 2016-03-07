<?php

namespace Application\Utility;

use \Closure;

/**
 *
 * @author arstropica
 *        
 */
class Helper {

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
	public static function recursive_implode(array $array, $glue = ',', $include_keys = false, $trim_all = false)
	{
		$glued_string = '';
		
		// Recursively iterates array and adds key/value to glued string
		array_walk_recursive($array, function ($value, $key) use($glue, $include_keys, &$glued_string) {
			$include_keys and $glued_string .= $key . $glue;
			$glued_string .= $value . $glue;
		});
		
		// Removes last $glue from string
		strlen($glue) > 0 and $glued_string = substr($glued_string, 0, -strlen($glue));
		
		// Trim ALL whitespace
		$trim_all and $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);
		
		return (string) $glued_string;
	}

	/**
	 * Check if variable is a valid JSON formatted string
	 *
	 * @param string $json        	
	 *
	 * @return bool
	 */
	public static function is_json($json)
	{
		$decoded = @json_decode($json, true);
		return (is_array($decoded) && (json_last_error() == JSON_ERROR_NONE));
	}

	/**
	 * Check if variable is a closure
	 *
	 * @param \Closure $t        	
	 */
	public static function is_closure($t)
	{
		return is_object($t) && ($t instanceof Closure);
	}

	/**
	 * PHP function that converts a string into a camelCase string
	 *
	 * @param string $str        	
	 * @param array $noStrip        	
	 *
	 * @return string
	 */
	public static function camelCase($str, array $noStrip = [])
	{
		// non-alpha and non-numeric characters become spaces
		$str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
		$str = trim($str);
		// uppercase the first character of each word
		$str = ucwords($str);
		$str = str_replace(" ", "", $str);
		$str = lcfirst($str);
		
		return $str;
	}

	/**
	 * Checks if string is a valid date format.
	 *
	 * @param string $date        	
	 *
	 * @return bool
	 */
	public static function validateDate($date)
	{
		$stamp = strtotime($date);
		if (!is_numeric($stamp))
			return false;
		$month = date('m', $stamp);
		$day = date('d', $stamp);
		$year = date('Y', $stamp);
		if (checkdate($month, $day, $year))
			return true;
		return false;
	}

	/**
	 * Tests if an input is valid PHP serialized string.
	 *
	 * Checks if a string is serialized using quick string manipulation
	 * to throw out obviously incorrect strings. Unserialize is then run
	 * on the string to perform the final verification.
	 *
	 * Valid serialized forms are the following:
	 * <ul>
	 * <li>boolean: <code>b:1;</code></li>
	 * <li>integer: <code>i:1;</code></li>
	 * <li>double: <code>d:0.2;</code></li>
	 * <li>string: <code>s:4:"test";</code></li>
	 * <li>array: <code>a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}</code></li>
	 * <li>object: <code>O:8:"stdClass":0:{}</code></li>
	 * <li>null: <code>N;</code></li>
	 * </ul>
	 *
	 * @author Chris Smith <code+php@chris.cs278.org>
	 * @copyright Copyright (c) 2009 Chris Smith (http://www.cs278.org/)
	 * @license http://sam.zoy.org/wtfpl/ WTFPL
	 * @param string $value
	 *        	test for serialized form
	 * @param mixed $result
	 *        	unserialize() of the $value
	 * @return boolean if $value is serialized data, otherwise false
	 */
	public static function is_serialized($value, &$result = null)
	{
		// Bit of a give away this one
		if (!is_string($value)) {
			return false;
		}
		
		// Serialized false, return true. unserialize() returns false on an
		// invalid string or it could return false if the string is serialized
		// false, eliminate that possibility.
		if ($value === 'b:0;') {
			$result = false;
			return true;
		}
		
		$length = strlen($value);
		$end = '';
		
		switch ($value [0]) {
			case 's' :
				if ($value [$length - 2] !== '"') {
					return false;
				}
			case 'b' :
			case 'i' :
			case 'd' :
				// This looks odd but it is quicker than isset()ing
				$end .= ';';
			case 'a' :
			case 'O' :
				$end .= '}';
				
				if ($value [1] !== ':') {
					return false;
				}
				
				switch ($value [2]) {
					case 0 :
					case 1 :
					case 2 :
					case 3 :
					case 4 :
					case 5 :
					case 6 :
					case 7 :
					case 8 :
					case 9 :
						break;
					
					default :
						return false;
				}
			case 'N' :
				$end .= ';';
				
				if ($value [$length - 1] !== $end [0]) {
					return false;
				}
				break;
			
			default :
				return false;
		}
		
		if (($result = @unserialize($value)) === false) {
			$result = null;
			return false;
		}
		return true;
	}

	/*
	 * Modifies a string to remove all non ASCII characters and spaces.
	 * Try slugify("é&asd_æô") for example
	 */
	public static function slugify($text)
	{
		// replace non letter or digits by -
		$text = preg_replace('~[^\\pL\d]+~u', '-', $text);
		// trim
		$text = trim($text, '-');
		// transliterate
		if (function_exists('iconv')) {
			$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		}
		// lowercase
		$text = strtolower($text);
		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);
		if (empty($text)) {
			return 'n-a';
		}
		return $text;
	}

	/**
	 *
	 * Validate IPv4 Address
	 *
	 * @param string $ip        	
	 * @param bool $return        	
	 *
	 * @return bool|string
	 */
	public static function validate_ipv4($ip, $return = true)
	{
		if ($return) {
			return filter_var($ip, FILTER_VALIDATE_IP);
		} else {
			return (!filter_var($ip, FILTER_VALIDATE_IP) === false);
		}
	}

	/**
	 * Add protocol
	 *
	 * @param string $uri        	
	 *
	 * @return string $uri
	 */
	public static function add_protocol($uri, $protocol = 'http')
	{
		if (!preg_match("~^(?:f|ht)tps?://~i", $uri)) {
			$protocol = preg_replace('/[^\w]/i', '', $protocol);
			$uri = $protocol . "://" . $uri;
		}
		return $uri;
	}

}

?>