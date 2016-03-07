<?php

namespace Application\Service;

use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Provider\CacheAwareTrait;

/**
 *
 * @author arstropica
 *        
 */
class SessionHistoryService {
	
	use ServiceLocatorAwareTrait, CacheAwareTrait;
	
	private $history;

	public function __construct(ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
	}

	public function getHistory()
	{
		return $this->getSessionHistory();
	}

	public function setHistory($entry, $unique = true)
	{
		$history = $this->getSessionHistory();
		if ($unique) {
			if ($entry != end($history)) {
				$history [] = $entry;
			}
		} else {
			$history [] = $entry;
		}
		$this->setSessionHistory($history);
		return $this;
	}

	public function resetHistory()
	{
		$this->setSessionHistory([ ]);
		return $this;
	}

	protected function getSessionHistory()
	{
		if (!$this->history) {
			$cache = $this->getSessionCache('history');
			$history = $cache->_history;
			if (!$history) {
				$history = [ ];
			}
			$this->history = $history;
		}
		return $this->history;
	}

	protected function setSessionHistory($history)
	{
		$this->history = $history;
		$cache = $this->getSessionCache('history');
		$cache->_history = $history;
	}

}

?>