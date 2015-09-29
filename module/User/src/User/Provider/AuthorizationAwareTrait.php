<?php
namespace User\Provider;
use OAuth2\Server as OAuth2Server;
use OAuth2\Request as OAuth2Request;

/**
 *
 * @author arstropica
 *        
 */
trait AuthorizationAwareTrait
{

	/**
	 *
	 * @var OAuth2Server
	 */
	protected $server;

	protected function authorize ()
	{
		if (! $this->getOAuth2Server()->verifyResourceRequest(
				OAuth2Request::createFromGlobals())) {
			// Not authorized
			return false;
		}
		return true;
	}

	/**
	 * Check if current user is authorized
	 *
	 * @return boolean
	 */
	protected function isUserAuthorized ($_roles = ['user', 'moderator', 'administrator'])
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

	/**
	 * Get OAuth2Server from Service Manager
	 *
	 * @return OAuth2Server
	 */
	protected function getOAuth2Server ()
	{
		if (! $this->server) {
			$server = $this->getServiceLocator()->get('OAuth2Server');
			$this->setOAuth2Server($server);
		}
		return $this->server;
	}

	/**
	 * Set OAuth2Server
	 *
	 * @param OAuth2Server $server        	
	 *
	 * @return void
	 */
	protected function setOAuth2Server (OAuth2Server $server)
	{
		$this->server = $server;
	}
}

?>