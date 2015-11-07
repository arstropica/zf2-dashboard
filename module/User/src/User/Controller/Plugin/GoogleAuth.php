<?php
namespace User\Controller\Plugin;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Provider\EntityManagerAwareTrait;

/**
 *
 * @author arstropica
 *        
 */
class GoogleAuth extends AbstractPlugin implements ServiceLocatorAwareInterface
{
	use EntityManagerAwareTrait, ServiceLocatorAwareTrait;

	protected $client;
	
	/**
	 * 
	 * @var ServiceLocatorInterface
	 */
	protected $parentLocator;
	
	public function __construct (ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
		$this->parentLocator = $serviceLocator;
	}

	public function __invoke($token)
	{
		return $this->isGoogleAuthorized($token);
	}
	
	public function isGoogleAuthorized ($token)
	{
		$this->setServiceLocator($this->parentLocator);
		$result = 0;
		$token = is_string($token) ? $token : @json_encode($token);
		if ($token) {
			$user = false;
			$client = $this->getClient();
			$client->setAccessToken($token);
			try {
				// resets token if expired
				if ($client->isAccessTokenExpired()) {
					return $result;
				} else {
					try {
						$service = new \Google_Service_Oauth2($client);
						$user = $service->userinfo->get(); // get user info
					} catch (\Exception $e) {}
					if ($user) {
						$result = $this->validateUser($user) ? 1 : 0;
					}
				}
			} catch (\Exception $e) {}
		}
		return $result;
	}

	protected function getClient ()
	{
		if (! $this->client) {
			$this->client = $this->parentLocator->get('GoogleClient');
		}
		return $this->client;
	}

	protected function validateUser ($gUser)
	{
		if (! $gUser)
			return false;
		$email = $gUser->email;
		$authorized_roles = array(
				'user',
				'administrator',
				'moderator'
		);
		$is_valid = false;
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository('User\Entity\User');
		$user = $objRepository->findOneBy(array(
				'email' => $email
		));
		if ($user) {
			$roles = $user->getRoles();
			$is_valid = array_filter($roles, 
					function  ($role) use( $authorized_roles)
					{
						return in_array($role->getRoleId(), $authorized_roles);
					});
		}
		
		return $is_valid ? true : false;
	}
}