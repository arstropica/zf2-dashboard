<?php
namespace User\Provider;

/**
 *
 * @author arstropica
 *        
 */
trait IdentityAwareTrait
{

	protected $identity;

	protected $role;

	public function getIdentity ()
	{
		if (null == $this->identity) {
			$this->identity = $this->zfcUserAuthentication()
				->getAuthService()
				->getIdentity();
		}
		return $this->identity;
	}

	public function getRole ()
	{
		if (null == $this->role) {
			$this->role = $this->getIdentity()->getRole();
		}
		return $this->role;
	}

	public function isAdmin ()
	{
		return ('administrator' === $this->getRole());
	}

	public function isGuest ()
	{
		return ('guest' === $this->getRole());
	}
}

?>