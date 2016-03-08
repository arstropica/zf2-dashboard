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
use Application\Utility\Helper;
use Lead\Entity\LeadAttributeValue;
use Lead\Entity\Lead;
use Report\Entity\Result;

class AbstractCrudController extends BaseController {
	
	use EntityManagerAwareTrait, ServiceEventTrait;
	
	protected $sessionContainers;

	public function getMatchedRoute()
	{
		$routeMatch = $this->getServiceLocator()
			->get('Application')
			->getMvcEvent()
			->getRouteMatch();
		return $routeMatch->getMatchedRouteName();
	}

	public function getEntityService()
	{
		$args = func_get_args();
		$entityClass = isset($args [0]) ? $args [0] : false;
		if (!isset($this->entityService)) {
			$entityServiceClass = $this->getEntityServiceClass($entityClass);
			if (!class_exists($entityServiceClass)) {
				throw new \RuntimeException("Classe $entityServiceClass inexistente!");
			}
			$this->entityService = new $entityServiceClass();
			$this->entityService->setServiceLocator($this->getServiceLocator());
		}
		
		return $this->entityService;
	}

	public function getEntityServiceClass()
	{
		$args = func_get_args();
		$entityClass = isset($args [0]) ? $args [0] : false;
		
		$module = $this->getModuleName();
		
		$entityClass = $entityClass ?  : $module;
		
		return "$module\Service\\$entityClass";
	}

	public function getSession($name)
	{
		if (null === $this->sessionContainers) {
			$this->sessionContainers = [ ];
		}
		if (!isset($this->sessionContainers [$name])) {
			$this->sessionContainers [$name] = new SessionContainer($name);
		}
		
		return $this->sessionContainers [$name];
	}

	public function handlePager()
	{
		$limit = $this->params()
			->fromPost('limit');
		if ($limit) {
			$sessionPager = $this->getSession('pager');
			$sessionPager->limit = $limit;
			return true;
		}
		return false;
	}

	public function setHistory()
	{
		$referrer = $this->getRequest()
			->getHeader('Referer');
		
		if ($referrer) {
			$url = $referrer->getUri();
			$this->SessionHistory($url);
		} else {
			$url = $this->getRequest()
				->getRequestUri();
		}
		
		return $url;
	}

	public function getHistory($last = true)
	{
		$history = $this->SessionHistory();
		if ($history) {
			return ($last) ? end($history) : $history;
		}
		return false;
	}

	public function getHistoricalRedirect($action = 'list', $current = false)
	{
		$history = $this->getHistory();
		$uri = $this->getRequest()
			->getRequestUri();
		return ($history && !$current && $uri != $history) ? $this->redirect()
			->toUrl($history) : $this->redirect()
			->toRoute($this->getActionRoute($action), [ ], true);
	}

	public function getLimit($default = 10)
	{
		$limit = $default;
		$sessionPager = $this->getSession('pager');
		if ($sessionPager && $sessionPager->limit) {
			$limit = $sessionPager->limit;
		}
		return $limit;
	}

	protected function getPagerForm($limit = 10, $values = array())
	{
		$sl = $this->getServiceLocator();
		$form = $sl->get('Application\Form\PagerForm');
		$data = [ 
				'limit' => $limit 
		];
		if ($data) {
			$form->setData($data);
			if (!$form->isValid()) {
				$form->setData(array ());
			}
		}
		
		return $form;
	}

	protected function getRedirect($route = false, $params = [], $options = [], $reuseMatchedParams = true)
	{
		$route = $route ?  : $this->getMatchedRoute();
		
		return $this->redirect()
			->toRoute($route, [ 
				'controller' => $this->params('controller'),
				'action' => $this->params('action') 
		], $options, $reuseMatchedParams);
	}

	protected function formatFormMessages($form, $glue = ". <br>\n", $debug = false)
	{
		$messages = $form->getMessages();
		$output = [ ];
		foreach ( $messages as $field => $notice ) {
			foreach ( $notice as $rule => $message ) {
				if (is_array($message)) {
					$output [] = "The field \"{$field}\" is invalid. " . ($debug ? $glue . Helper::recursive_implode($message, $glue) : "");
				} else {
					$output [] = "The field \"{$field}\" is invalid. {$message}";
				}
			}
		}
		return implode($glue, $output);
	}

	protected function validateSubmit($post)
	{
		if (is_array($post) && array_key_exists('confirm', $post)) {
			$confirm = $post ['confirm'];
			
			if ($confirm == "1") {
				return true;
			}
		}
		
		return false;
	}

	protected function extractAttributes(Array $results, $filter = true)
	{
		$attributes = [ ];
		
		if ($results && $filter) {
			foreach ( $results as $entity ) {
				$lead = false;
				if ($entity instanceof Lead) {
					$lead = $entity;
				} elseif ($entity instanceof Result) {
					$lead = $entity->getLead();
				}
				if ($lead instanceof Lead) {
					$values = $lead->getAttributes();
					if ($values) {
						foreach ( $values as $value ) {
							if ($value instanceof LeadAttributeValue) {
								$attribute = $value->getAttribute();
								if ($attribute) {
									$attributes [$attribute->getId()] = $attribute->getAttributeDesc();
								}
							}
						}
					}
				}
			}
		} else {
			$em = $this->getEntityManager();
			$attributeRepository = $em->getRepository("Lead\\Entity\\LeadAttribute");
			
			$attributes = $attributeRepository->getUniqueArray();
		}
		return $attributes;
	}

}

?>