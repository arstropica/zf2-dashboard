<?php
namespace User\Controller\Plugin;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 *
 * @author arstropica
 *
 */
class GoogleUser extends AbstractPlugin implements ServiceLocatorAwareInterface
{
	use ServiceLocatorAwareTrait;
 
 	protected $client;

	public function __construct (ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
	}

	public function __invoke ($client)
	{
		try {
			$service = new \Google_Service_Oauth2($client);
			$user = $service->userinfo->get(); //get user info 
		  	if ($user) {
				return $user;
			}
			 return false;
		} catch (\Exception $e) {
			return false;
		}
	}
  
  protected function getClient()
  {
    	if (!$this->client) {
    		$this->client = $this->getServiceLocator()->get('GoogleClient');
    	}
		return $this->client;
  }

}