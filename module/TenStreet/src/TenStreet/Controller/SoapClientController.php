<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/TenStreet for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace TenStreet\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use OAuth2\Server as OAuth2Server;
use OAuth2\Request as OAuth2Request;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Provider\EntityManagerAwareTrait;
use Lead\Entity\Lead;
use Event\Entity\Event;

class SoapClientController extends AbstractActionController
{
	
	use EntityManagerAwareTrait;

	/**
	 *
	 * @var OAuth2Server
	 */
	protected $server;

	public function __construct (OAuth2Server $server, 
			ServiceLocatorInterface $sm)
	{
		$this->server = $server;
	}

	protected function authorize ()
	{
		if (! $this->server->verifyResourceRequest(
				OAuth2Request::createFromGlobals())) {
			// Not authorized return 401 error
			return false;
		}
		return true;
	}

	public function indexAction ()
	{
		if (! $this->authorize() && ! $this->isUserAuthorized()) {
			return $this->getJsonErrorResponse('json')->insufficientAuthorization();
		}
		return $this->getJsonErrorResponse('json')->methodNotAllowed();
	}

	public function sendAction ()
	{
		if (! $this->authorize() && ! $this->isUserAuthorized()) {
			return $this->getJsonErrorResponse('json')->insufficientAuthorization();
		}
		
		$id = (int) $this->params()->fromRoute('id', 0);
		
		if (! $id) {
			return $this->getJsonErrorResponse('json')->missingParameter();
		}
		
		$result = $this->getServiceLocator()
			->get('TenStreet\Service\PostClientData')
			->send($id);
		
		return $this->getJsonErrorResponse(
				'json')->successOperation($result);
	}

	public function preDispatch ()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}

	protected function isUserAuthorized ()
	{
		$role = false;
		if ($this->zfcUserAuthentication()
			->getAuthService()
			->hasIdentity()) {
			$roles = $this->serviceLocator->get(
					'BjyAuthorize\Provider\Identity\ProviderInterface')->getIdentityRoles();
			
			if ($roles && is_array($roles)) {
				$role = $roles[0]->getRoleId();
			}
			if (in_array($role, 
					[
							'administrator',
							'moderator',
							'user'
					])) {
				return true;
			} else {
				return false;
			}
		}
		return $role;
	}
}
