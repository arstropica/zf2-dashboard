<?php
namespace Application\Controller;

/**
 *
 * @author arstropica
 *        
 */
use LosBase\Controller\AbstractCrudController as BaseController;
use Zend\Session\Container as SessionContainer;
use Application\Provider\EntityManagerAwareTrait;
use Application\Provider\ServiceEventTrait;

class AbstractCrudController extends BaseController
{
	
	use EntityManagerAwareTrait, ServiceEventTrait;

	protected $sessionContainers;

	public function getMatchedRoute ()
	{
		$routeMatch = $this->getServiceLocator()
			->get('Application')
			->getMvcEvent()
			->getRouteMatch();
		return $routeMatch->getMatchedRouteName();
	}

	public function getSession ($name)
	{
		if (null === $this->sessionContainers) {
			$this->sessionContainers = [];
		}
		if (! isset($this->sessionContainers[$name])) {
			$this->sessionContainers[$name] = new SessionContainer($name);
		}
		
		return $this->sessionContainers[$name];
	}

	public function handlePager ()
	{
		$limit = $this->params()->fromPost('limit');
		if ($limit) {
			$sessionPager = $this->getSession('pager');
			$sessionPager->limit = $limit;
			return true;
		}
		return false;
	}

	public function getLimit ($default = 10)
	{
		$limit = $default;
		$sessionPager = $this->getSession('pager');
		if ($sessionPager && $sessionPager->limit) {
			$limit = $sessionPager->limit;
		}
		return $limit;
	}

	protected function getPagerForm ($limit = 10, $values = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('Application\Form\PagerForm');
		$data = [
				'limit' => $limit
		];
		if ($data) {
			$form->setData($data);
			if (! $form->isValid()) {
				$form->setData(array());
			}
		}
		
		return $form;
	}

	protected function getRedirect ($route = false, $params = [], $options = [], 
			$reuseMatchedParams = true)
	{
		$route = $route ?  : $this->getMatchedRoute();
		
		return $this->redirect()->toRoute($route, 
				[
						'controller' => $this->params('controller'),
						'action' => $this->params('action')
				], $options, $reuseMatchedParams);
	}
}

?>