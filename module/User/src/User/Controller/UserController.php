<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/User for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace User\Controller;

use Application\Controller\AbstractCrudController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Application\Provider\CacheAwareTrait;
use Application\Service\CacheAwareInterface;

class UserController extends AbstractCrudController implements CacheAwareInterface {
	use CacheAwareTrait;
	
	/**
	 *
	 * @var StorageInterface
	 *
	 *
	 */
	protected $cache;
	
	protected $default_error_message = 'Target Media Partners encountered an error when trying to validate access.';

	public function indexAction()
	{
		return array ();
	}

	public function fooAction()
	{
		// This shows the :controller and :action parameters in default route
		// are working when you browse to /user/user/foo
		return array ();
	}

	public function authAction()
	{
		$state = false;
		$result = array ();
		$post = $this->params()
			->fromPost();
		$query = $this->params()
			->fromQuery();
		$client = $this->getClient();
		if (($token = $client->getAccessToken()) == true) {
			$result ['token'] = $token;
		} elseif (isset($query ['code'])) {
			try {
				$client->authenticate($query ['code']);
				$result ['token'] = $client->getAccessToken();
			} catch ( \Exception $e ) {
				$result ['error'] = $e->getMessage();
			}
		}
		
		if (isset($query ['state'])) {
			$query ['state'] = $state = json_decode(base64_decode($query ['state']), true);
			if ($state && isset($state ['id'], $state ['referrer'], $state ['origin'], $query ['code'])) {
				$state ['code'] = $query ['code'];
				$cache = $this->getCache();
				$cache->setItem($state ['id'], array_merge($state, $result));
			}
		}
		return new ViewModel([ 
				'post' => $post,
				'query' => $query 
		]);
	}

	public function tokenAction()
	{
		$result = array (
				'outcome' => 0,
				'message' => 'Not permitted.' 
		);
		return new JsonModel($result);
	}

	public function exchangeAction()
	{
		$result = array ();
		$token = false;
		$id = $this->params()
			->fromPost('id', 0);
		$origin = $this->params()
			->fromPost('origin', '');
		
		if ($id && $origin) {
			try {
				$client = $this->getClient();
				$cache = $this->getCache();
				$state = $cache->getItem($id);
				if ($state && $origin && $state ['origin'] == $origin) {
					$code = $state ['code'] || false;
					if (($token = $client->getAccessToken()) == true) {
						$result ['token'] = $token;
					} elseif (isset($state ['token'])) {
						$token = $state ['token'];
						$cache->removeItem($id);
					} else {
						$result ['error'] = $this->default_error_message . " The Token was not saved.";
					}
					if ($token) {
						try {
							$client->setAccessToken($token);
							$user = $this->GoogleUser($client);
							if ($user) {
								$valid = $this->validateUser($user) ? 1 : 0;
								if ($valid) {
									$result ['token'] = $token;
									$result ['outcome'] = 1;
									$result ['message'] = 'Token successfully retrieved for ' . $user->email;
								} else {
									$result ['error'] = $user->email . ' is not authorized.';
								}
							} else {
								$result ['error'] = 'Access could not be granted.  This is a result of an invalid access token or user.';
							}
						} catch ( \Exception $e ) {
							$result ['error'] = $this->default_error_message . " " . $e->getMessage();
						}
					} else {
						$result ['error'] = $this->default_error_message . " No Token could be found.";
					}
				} else {
					$result ['error'] = $this->default_error_message . " The cache was not saved.";
				}
			} catch ( \Exception $e ) {
				$result ['error'] = $e->getMessage();
			}
		} else {
			$result ['error'] = $this->default_error_message . " The ID or Origin are missing.";
		}
		if (!isset($result ['outcome'])) {
			$result ['outcome'] = 0;
		}
		
		return new JsonModel($result);
	}

	public function refreshAction()
	{
		$result = array ();
		$token = false;
		$token = $this->params()
			->fromPost('token', false);
		if ($token) {
			$json_token = $this->validateToken($token, true);
			if ($json_token) {
				$token = json_decode($json_token);
				$client = $this->getClient();
				$client->setAccessToken($json_token);
				try {
					// resets token if expired
					if ($client->isAccessTokenExpired()) {
						$refresh_token = $client->getRefreshToken();
						if ($refresh_token) {
							$client->refreshToken($refresh_token);
							$token = $client->getAccessToken();
						}
					}
					
					$user = $this->GoogleUser($client);
					if ($user) {
						$valid = $this->validateUser($user) ? 1 : 0;
						if ($valid) {
							$result ['token'] = $token;
							$result ['outcome'] = 1;
							$result ['message'] = 'Token successfully refreshed for ' . $user->email;
						} else {
							$result ['error'] = $user->email . ' is not authorized.';
						}
					} else {
						$result ['error'] = 'Access could not be granted.  This is a result of an invalid access token or user.';
					}
				} catch ( \Exception $e ) {
					$result ['error'] = $e->getMessage();
				}
			} else {
				$result ['error'] = 'The token is malformed.';
				$result ['debug'] = $json_token;
			}
		} else {
			$result ['error'] = 'The token is missing.';
		}
		if (!isset($result ['outcome'])) {
			$result ['outcome'] = 0;
		}
		return new JsonModel($result);
	}

	public function validAction()
	{
		$result = array ();
		$token = false;
		$valid = 0;
		$token = $this->params()
			->fromPost('token', false);
		if ($token) {
			$json_token = $this->validateToken($token, true);
			if ($json_token) {
				$valid = $this->isGoogleAuthorized($json_token);
				$result ['outcome'] = $valid;
				if (!$valid) {
					$result ['error'] = 'The token has either expired or is invalid.';
					$result ['debug'] = $token;
				}
			} else {
				$result ['error'] = 'The token is malformed.';
				$result ['debug'] = $json_token;
			}
		} else {
			$result ['error'] = 'The token is missing.';
		}
		if (!isset($result ['outcome'])) {
			$result ['outcome'] = 0;
		}
		return new JsonModel($result);
	}

	public function revokeAction()
	{
		$result = array ();
		$token = false;
		$valid = 0;
		$token = $this->params()
			->fromPost('token', false);
		if ($token) {
			$json_token = $this->validateToken($token, true);
			if ($json_token) {
				$client = $this->getClient();
				$client->setAccessToken($json_token);
				try {
					$revoked = $client->revokeToken();
					$result ['outcome'] = $revoked ? 1 : 0;
					if (!$revoked) {
						$result ['error'] = 'The token is invalid.';
						$result ['debug'] = $json_token;
					}
				} catch ( \Exception $e ) {
					$result ['error'] = $e->getMessage();
				}
			} else {
				$result ['error'] = 'The token is malformed.';
				$result ['debug'] = $token;
			}
		} else {
			$result ['error'] = 'The token is missing.';
		}
		if (!isset($result ['outcome'])) {
			$result ['outcome'] = 0;
		}
		return new JsonModel($result);
	}

	protected function validateToken($token, $toJSON = true)
	{
		$result = false;
		if ($token) {
			$_token = $token;
			
			$double_decoded_token = $decoded_token = $json_token = null;
			if (is_string($token)) {
				$json_token = $token;
				
				if (($decoded_token = @json_decode($json_token)) == true) {
					// Double Encode Fix
					if (is_string($decoded_token)) {
						$encoded_token = $decoded_token;
						$double_decoded_token = @json_decode($encoded_token);
						if ($double_decoded_token) {
							$json_token = $encoded_token;
						}
					}
				} else {
					$json_token = false;
				}
			} else {
				$json_token = @json_encode($token);
			}
			
			if ($json_token) {
				$token = @json_decode($json_token);
				if ($token) {
					$result = $toJSON ? $json_token : $token;
				}
			}
		}
		return $result;
	}

	protected function validateUser($gUser)
	{
		if (!$gUser)
			return false;
		$email = $gUser->email;
		$authorized_roles = array (
				'user',
				'administrator',
				'moderator' 
		);
		$is_valid = false;
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($this->getEntityClass());
		$user = $objRepository->findOneBy(array (
				'email' => $email 
		));
		
		if ($user) {
			$roles = $user->getRoles();
			$is_valid = array_filter($roles, function ($role) use($authorized_roles) {
				return in_array($role->getRoleId(), $authorized_roles);
			});
		}
		
		return $is_valid ? true : false;
	}

	/**
	 * Return GoogleClient
	 *
	 * @return \Google_Client
	 */
	protected function getClient()
	{
		return $this->getServiceLocator()
			->get('GoogleClient');
	}
}
