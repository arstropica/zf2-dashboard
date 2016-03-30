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
		
		switch ($value[0]) {
			case 's' :
				if ($value[$length - 2] !== '"') {
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
				
				if ($value[1] !== ':') {
					return false;
				}
				
				switch ($value[2]) {
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
				
				if ($value[$length - 1] !== $end[0]) {
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
	 * Try slugify("�&asd_��") for example
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

	/**
	 * Parse / Validate Phone Number
	 *
	 * @param string|number $raw        	
	 * @param string $format        	
	 *
	 * @return boolean|string|array
	 */
	public static function parse_phonenumber($raw, $format = 'string')
	{
		$result = false;
		$raw = trim($raw);
		$pattern = '~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*$~';
		if (preg_match($pattern, $raw, $matches)) {
			unset($matches[0]);
			$result = $format == 'string' ? implode("", $matches) : $matches;
		}
		return $result;
	}

	/**
	 *
	 * @param string $code        	
	 *
	 * @return boolean|string
	 */
	public static function area_code_to_state($code)
	{
		$ac_json = '{"201":"NJ","202":"DC","203":"CT","204":"MB","205":"AL","206":"WA","207":"ME","208":"ID","209":"CA","210":"TX","211":false,"212":"NY","213":"CA","214":"TX","215":"PA","216":"OH","217":"IL","218":"MN","219":"IN","224":"IL","225":"LA","226":"ON","228":"MS","229":"GA","231":"MI","234":"OH","236":"VA","239":"FL","240":"MD","242":false,"246":false,"248":"MI","250":"BC","251":"AL","252":"NC","253":"WA","254":"TX","256":"AL","260":"IN","262":"WI","264":false,"267":"PA","268":false,"269":"MI","270":"KY","276":"VA","278":"MI","281":"TX","283":"OH","284":false,"289":"ON","301":"MD","302":"DE","303":"CO","304":"WV","305":"FL","306":"SK","307":"WY","308":"NE","309":"IL","310":"CA","311":false,"312":"IL","313":"MI","314":"MO","315":"NY","316":"KS","317":"IN","318":"LA","319":"IA","320":"MN","321":"FL","323":"CA","325":"TX","330":"OH","331":"IL","334":"AL","336":"NC","337":"LA","339":"MA","340":"VI","341":"CA","345":false,"347":"NY","351":"MA","352":"FL","360":"WA","361":"TX","369":"CA","380":"OH","385":"UT","386":"FL","401":"RI","402":"NE","403":"AB","404":"GA","405":"OK","406":"MT","407":"FL","408":"CA","409":"TX","410":"MD","411":false,"412":"PA","413":"MA","414":"WI","415":"CA","416":"ON","417":"MO","418":"QC","419":"OH","423":"TN","424":"CA","425":"WA","430":"TX","432":"TX","434":"VA","435":"UT","438":"QC","440":"OH","441":false,"442":"CA","443":"MD","450":"QC","456":false,"464":"IL","469":"TX","470":"GA","473":false,"475":"CT","478":"GA","479":"AR","480":"AZ","484":"PA","500":false,"501":"AR","502":"KY","503":"OR","504":"LA","505":"NM","506":"NB","507":"MN","508":"MA","509":"WA","510":"CA","511":false,"512":"TX","513":"OH","514":"QC","515":"IA","516":"NY","517":"MI","518":"NY","519":"ON","520":"AZ","530":"CA","540":"VA","541":"OR","551":"NJ","555":false,"557":"MO","559":"CA","561":"FL","562":"CA","563":"IA","564":"WA","567":"OH","570":"PA","571":"VA","573":"MO","574":"IN","575":"NM","580":"OK","585":"NY","586":"MI","600":false,"601":"MS","602":"AZ","603":"NH","604":"BC","605":"SD","606":"KY","607":"NY","608":"WI","609":"NJ","610":"PA","611":false,"612":"MN","613":"ON","614":"OH","615":"TN","616":"MI","617":"MA","618":"IL","619":"CA","620":"KS","623":"AZ","626":"CA","627":"CA","628":"CA","630":"IL","631":"NY","636":"MO","641":"IA","646":"NY","647":"ON","649":false,"650":"CA","651":"MN","660":"MO","661":"CA","662":"MS","664":false,"669":"CA","670":"MP","671":"GU","678":"GA","679":"MI","682":"TX","684":false,"689":"FL","700":false,"701":"ND","702":"NV","703":"VA","704":"NC","705":"ON","706":"GA","707":"CA","708":"IL","709":"NL","710":false,"711":false,"712":"IA","713":"TX","714":"CA","715":"WI","716":"NY","717":"PA","718":"NY","719":"CO","720":"CO","724":"PA","727":"FL","731":"TN","732":"NJ","734":"MI","737":"TX","740":"OH","747":"CA","754":"FL","757":"VA","758":false,"760":"CA","762":"GA","763":"MN","764":"CA","765":"IN","767":false,"769":"MS","770":"GA","772":"FL","773":"IL","774":"MA","775":"NV","778":"BC","779":"IL","780":"AB","781":"MA","784":false,"785":"KS","786":"FL","787":"PR","800":false,"801":"UT","802":"VT","803":"SC","804":"VA","805":"CA","806":"TX","807":"ON","808":"HI","809":false,"810":"MI","811":false,"812":"IN","813":"FL","814":"PA","815":"IL","816":"MO","817":"TX","818":"CA","819":"QC","822":false,"828":"NC","829":false,"830":"TX","831":"CA","832":"TX","833":false,"835":"PA","843":"SC","844":false,"845":"NY","847":"IL","848":"NJ","850":"FL","855":false,"856":"NJ","857":"MA","858":"CA","859":"KY","860":"CT","862":"NJ","863":"FL","864":"SC","865":"TN","866":false,"867":"YT","868":false,"869":false,"870":"AR","872":"IL","876":false,"877":false,"878":"PA","880":false,"881":false,"882":false,"888":false,"898":false,"900":false,"901":"TN","902":"NS","903":"TX","904":"FL","905":"ON","906":"MI","907":"AK","908":"NJ","909":"CA","910":"NC","911":false,"912":"GA","913":"KS","914":"NY","915":"TX","916":"CA","917":"NY","918":"OK","919":"NC","920":"WI","925":"CA","927":"FL","928":"AZ","931":"TN","935":"CA","936":"TX","937":"OH","939":"PR","940":"TX","941":"FL","947":"MI","949":"CA","951":"CA","952":"MN","954":"FL","956":"TX","957":"NM","959":"CT","970":"CO","971":"OR","972":"TX","973":"NJ","975":"MO","976":false,"978":"MA","979":"TX","980":"NC","984":"NC","985":"LA","989":"MI","999":false}';
		$area_codes = json_decode($ac_json, true);
		return isset($area_codes[$code]) ? $area_codes[$code] : false;
	}
}

?>