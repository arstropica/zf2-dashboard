<?php

namespace Application\Provider;

use Doctrine\ORM\Proxy\Proxy;
use Application\Service\ElasticSearch\SearchableEntityInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @author arstropica
 *        
 */
trait EntityDataTrait {

	/**
	 *
	 * @return array
	 */
	public function toArray()
	{
		$data = get_object_vars($this);
		
		$data = array_filter($data, function ($element) {
			return !is_object($element);
		});
		
		if ($this instanceof Proxy) {
			$originClassName = get_parent_class($this);
			
			foreach ( $data as $key => $value ) {
				if (!property_exists($originClassName, $key)) {
					unset($data [$key]);
				}
			}
		}
		
		if ($this instanceof SearchableEntityInterface) {
			unset($data ['searchManager']);
		}
		
		if ($this instanceof ServiceLocatorInterface) {
			unset($data ['serviceLocator']);
		}
		
		foreach ( $data as $key => $value ) {
			$slug = ucfirst(preg_replace('/^[^\w]/i', '', $key));
			if (method_exists($this, 'get' . $slug)) {
				try {
					$value = $this->{'get' . $slug}();
					if ($value instanceof \DateTime) {
						$value = $value->format('c');
					}
					if (!is_object($value)) {
						$data [$key] = $value;
					}
				} catch ( \Exception $e ) {
					continue;
				}
			}
		}
		
		return $data;
	}
}

?>