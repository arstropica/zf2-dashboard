<?php
namespace TenStreet\Controller\Plugin;

class SoapClient extends \SoapClient
{

	public function __doRequest ($request, $location, $action, 
			$version = SOAP_1_1, $one_way = NULL)
	{
		$xml = explode("\r\n", 
				parent::__doRequest(trim($request), $location, $action, $version, $one_way));
		
		$response = preg_replace(
				'/^(\x00\x00\xFE\xFF|\xFF\xFE\x00\x00|\xFE\xFF|\xFF\xFE|\xEF\xBB\xBF)/', 
				"", $xml[0]);
		
		return $response;
	}

	function strip_bom ($str)
	{
		return preg_replace(
				'/^(\x00\x00\xFE\xFF|\xFF\xFE\x00\x00|\xFE\xFF|\xFF\xFE|\xEF\xBB\xBF)/', 
				"", $str);
	}
}