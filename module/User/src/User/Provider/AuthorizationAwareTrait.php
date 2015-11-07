<?php
namespace User\Provider;
use OAuth2\Server as OAuth2Server;
use OAuth2\Request as OAuth2Request;
use User\Service\GoogleAuth;

/**
 *
 * @author arstropica
 *        
 */
trait AuthorizationAwareTrait
{

	protected function authorize ()
	{
		$authorized = false;
		
		/* @var $server OAuth2Server */
		$server = $this->getServiceLocator()->get('OAuth2Server');
		if ($server->verifyResourceRequest(OAuth2Request::createFromGlobals())) {
			// authorized
			$authorized = true;
		} else {
			$request = $this->getServiceLocator()->get('Request');
			$token = $request->getPost('token', false);
			if ($token) {
				/* @var $googleAuth GoogleAuth */
				$googleAuth = $this->getServiceLocator()
					->get('ControllerPluginManager')
					->get('isGoogleAuthorized');
				$authorized = $googleAuth->isGoogleAuthorized($token);
			}
		}
		return $authorized ? true : false;
	}

	/**
	 * Check if current user is authorized
	 *
	 * @return boolean
	 */
	protected function isUserAuthorized (
			$_roles = ['user', 'moderator', 'administrator'])
	{
		$authorized = false;
		if ($this->getServiceLocator()
			->get('ControllerPluginManager')
			->get('zfcUserAuthentication')
			->getAuthService()
			->hasIdentity()) {
			$roles = $this->getServiceLocator()
				->get('BjyAuthorize\Provider\Identity\ProviderInterface')
				->getIdentityRoles();
			
			if ($roles && is_array($roles)) {
				foreach ($roles as $role) {
					$id = $role->getRoleId();
				}
				if (in_array($id, $_roles)) {
					$authorized = true;
				}
			}
		}
		return $authorized;
	}
}

?>