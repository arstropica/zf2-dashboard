<?php
namespace Application\Provider;
use Doctrine\ORM\Proxy\Proxy;

/**
 *
 * @author arstropica
 *        
 */
trait EntityDataTrait
{

	/**
	 *
	 * @return array
	 */
	public function toArray ()
	{
		$data = get_object_vars($this);
		
		$data = array_filter($data, 
				function  ($element)
				{
					return ! is_object($element);
				});
		
		if ($this instanceof Proxy) {
			$originClassName = get_parent_class($this);
			
			foreach ($data as $key => $value) {
				if (! property_exists($originClassName, $key)) {
					unset($data[$key]);
				}
			}
		}
		
		foreach ($data as $key => $value) {
			if (method_exists($this, 'get' . ucfirst($key))) {
				$data[$key] = $this->{'get' . ucfirst($key)}();
			}
		}
		
		return $data;
	}
}

?>