<?php
namespace Application\Provider;

/**
 *
 * @author arstropica
 *        
 */
trait easyXMLTrait
{
	/**
	 *
	 * @param unknown $xml        	
	 */
	protected function xml2array ($xml)
	{
		return $this->getServiceLocator()
			->get('ControllerPluginManager')
			->get('easyXML')
			->xml2array($xml);
	}

	/**
	 *
	 * @param string $rootNode        	
	 * @param array $array        	
	 * @param boolean $encode        	
	 */
	protected function array2xml ($rootNode, $array, $encode = true)
	{
		$xml = $this->getServiceLocator()
			->get('ControllerPluginManager')
			->get('easyXML')
			->array2xml($rootNode, $array);
		$xmlObj = new \SimpleXMLElement($xml);
		$dom = dom_import_simplexml($xmlObj);
		$xml = $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
		return $encode ? "<![CDATA[" . $xml . "]]>" : $xml;
	}
}

?>