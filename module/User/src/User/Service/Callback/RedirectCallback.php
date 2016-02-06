<?php

namespace User\Service\Callback;

use Zend\Mvc\Application;
use Zend\Mvc\Router\RouteInterface;
use Zend\Http\PhpEnvironment\Response;
use ZfcUser\Options\ModuleOptions;
use Zend\Http\Request;
use Zend\Mvc\Router\Http\RouteMatch;

/**
 * Buils a redirect response based on the current routing and parameters
 */
class RedirectCallback {
	
	/**
	 *
	 * @var RouteInterface
	 */
	private $router;
	
	/**
	 *
	 * @var Application
	 */
	private $application;
	
	/**
	 *
	 * @var ModuleOptions
	 */
	private $options;

	/**
	 *
	 * @param Application $application        	
	 * @param RouteInterface $router        	
	 * @param ModuleOptions $options        	
	 */
	public function __construct(Application $application, RouteInterface $router, ModuleOptions $options)
	{
		$this->router = $router;
		$this->application = $application;
		$this->options = $options;
	}

	/**
	 *
	 * @return Response
	 */
	public function __invoke()
	{
		$routeMatch = $this->application->getMvcEvent()
			->getRouteMatch();
		$redirect = $this->getRedirect($routeMatch->getMatchedRouteName(), $this->getRedirectRouteFromRequest());
		
		$response = $this->application->getResponse();
		$response->getHeaders()
			->addHeaderLine('Location', $redirect);
		$response->setStatusCode(302);
		return $response;
	}

	/**
	 * Return the redirect from param.
	 * First checks GET then POST
	 *
	 * @return string
	 */
	private function getRedirectRouteFromRequest()
	{
		$request = $this->application->getRequest();
		$redirect = $request->getQuery('redirect');
		if ($redirect && $this->routeExists($redirect)) {
			return $redirect;
		}
		
		$redirect = $request->getPost('redirect');
		if ($redirect && $this->routeExists($redirect)) {
			return $redirect;
		}
		return false;
	}

	/**
	 *
	 * @param string $route        	
	 * @return bool|RouteMatch
	 */
	private function routeExists($route)
	{
		return $this->checkRoute($route);
	}

	/**
	 *
	 * @param string $route        	
	 * @return boolean|RouteMatch
	 */
	private function checkRoute($route)
	{
		$request = new Request();
		$request->setMethod(Request::METHOD_GET);
		$request->setUri($route);
		try {
			$match = $this->router->match($request);
			return isset($match) ? $match : false;
		} catch ( \Exception $e ) {
			return false;
		}
		return false;
	}

	/**
	 * Returns the url to redirect to based on current route.
	 * If $redirect is set and the option to use redirect is set to true, it
	 * will return the $redirect url.
	 *
	 * @param string $currentRoute        	
	 * @param bool $redirect        	
	 * @return mixed
	 */
	protected function getRedirect($currentRoute, $redirect = false)
	{
		$useRedirect = $this->options->getUseRedirectParameterIfPresent();
		$routeExists = ($redirect && $this->routeExists($redirect));
		if (!$useRedirect || !$routeExists) {
			$redirect = false;
		}
		
		switch ($currentRoute) {
			case 'zfcuser/register' :
			case 'zfcuser/login' :
			case 'zfcuser/authenticate' :
				if ($redirect && (($match = $this->routeExists($redirect)) == true)) {
					$route = $match->getMatchedRouteName();
					return $this->router->assemble(array (), array (
							'name' => $route 
					));
				} else {
					$route = $this->options->getLoginRedirectRoute();
					return $this->router->assemble(array (), array (
							'name' => $route 
					));
				}
				break;
			case 'zfcuser/logout' :
				$route = ($redirect) ?  : $this->options->getLogoutRedirectRoute();
				return $this->router->assemble(array (), array (
						'name' => $route 
				));
				break;
			default :
				return $this->router->assemble(array (), array (
						'name' => 'zfcuser' 
				));
		}
	}
}
