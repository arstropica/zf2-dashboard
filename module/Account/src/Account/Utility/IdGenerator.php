<?php
namespace Account\Utility;

/**
 *
 * @author arstropica
 *        
 */
class IdGenerator
{

	static private function _nextChar ()
	{
		return base_convert(mt_rand(0, 35), 10, 36);
	}

	static public function generate ()
	{
		$parts = explode('.', uniqid('', true));
		
		$id = str_pad(base_convert($parts[0], 16, 2), 56, mt_rand(0, 1), 
				STR_PAD_LEFT) . str_pad(base_convert($parts[1], 10, 2), 32, 
				mt_rand(0, 1), STR_PAD_LEFT);
		$id = str_pad($id, strlen($id) + (8 - (strlen($id) % 8)), mt_rand(0, 1), 
				STR_PAD_BOTH);
		
		$chunks = str_split($id, 8);
		
		$id = array();
		foreach ($chunks as $key => $chunk) {
			if ($key & 1) { // odd
				array_unshift($id, $chunk);
			} else { // even
				array_push($id, $chunk);
			}
		}
		
		// add random seeds
		$prefix = str_pad(base_convert(mt_rand(), 10, 36), 6, self::_nextChar(), 
				STR_PAD_BOTH);
		$id = str_pad(base_convert(implode($id), 2, 36), 19, self::_nextChar(), 
				STR_PAD_BOTH);
		$suffix = str_pad(base_convert(mt_rand(), 10, 36), 6, self::_nextChar(), 
				STR_PAD_BOTH);
		
		return $prefix . self::_nextChar() . $id . $suffix;
	}
}
