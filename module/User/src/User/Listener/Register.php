<?php
namespace User\Listener;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\Event;

class Register extends AbstractListenerAggregate
{

	public function attach (EventManagerInterface $events)
	{
		$sharedManager = $events->getSharedManager();
		$this->listeners[] = $sharedManager->attach('ZfcUser\Form\Register', 
				'init', array(
						$this,
						'onRegisterForm'
				));
		$this->listeners[] = $sharedManager->attach('ZfcUser\Service\User', 
				'register', array(
						$this,
						'onRegister'
				));
		$this->listeners[] = $sharedManager->attach('ZfcUser\Service\User', 
				'register.post', 
				array(
						$this,
						'onRegisterPost'
				));
	}

	public function onRegister (Event $e)
	{
		$user = $e->getParam('user');
		$form = $e->getParam('form');
		
		$username = $form->get('username')->getValue();
		$role = $this->getRole($e);
		
		if ($role !== null) {
			$user->addRole($role);
		}
		
		if ($username) {
			$_username = $username;
			for ($i = 0; $i < 3; $i ++) {
				if ($this->checkUserExists($e, $_username)) {
					$_username = $username . "_" . sprintf('%04d', 
							rand(0, 9999));
				} else {
					break;
				}
			}
			if (! $this->checkUserExists($e, $_username)) {
				$user->setUsername($_username);
				$this->addOAuthClient($e, $_username);
			}
		}
	}

	public function onRegisterPost (Event $e)
	{
		$user = $e->getParam('user');
		$form = $e->getParam('form');
		
		// Do something after user has registered
	}

	public function onRegisterForm (Event $e)
	{
		/* @var $form \ZfcUser\Form\Register */
		$form = $e->getTarget();
		$form->add(
				array(
						'name' => 'username',
						'options' => array(
								'label' => 'Username'
						),
						'attributes' => array(
								'type' => 'text'
						)
				));
		
		$form->add(
				array(
						'name' => 'roleid',
						'type' => 'Zend\Form\Element\Select',
						'required' => true,
						'options' => array(
								'label' => 'Role',
								'empty_option' => 'Choose a role',
								'value_options' => array(
										'guest' => 'Guest',
										'user' => 'User',
										'moderator' => 'Moderator',
										'administrator' => 'Administrator'
								)
						)
				));
	}

	public function getRole (Event $e)
	{
		$sm = $e->getTarget()->getServiceManager();
		$em = $sm->get('doctrine.entitymanager.orm_default');
		$form = $e->getParam('form');
		$roleId = $form->get('roleid')->getValue();
		
		if (! $roleId) {
			$config = $sm->get('config');
			$roleId = $config['zfcuser']['new_user_default_role'];
		}
		
		$criteria = array(
				'roleId' => $roleId
		);
		
		$role = $em->getRepository('User\Entity\Role')->findOneBy($criteria);
		return $role;
	}

	public function checkUserExists (Event $e, $username)
	{
		$sm = $e->getTarget()->getServiceManager();
		$mapper = $sm->get('User_user_mapper');
		$userObject = $mapper->findByUsername($username);
		
		return $userObject ? true : false;
	}

	public function addOAuthClient (Event $e, $username)
	{
		$result = false;
		$role = $this->getRole($e);
		
		if ($role && in_array($role->getRoleId(), 
				[
						'administrator',
						'moderator',
						'user'
				])) {
			$sm = $e->getTarget()->getServiceManager();
			$oAuth2Adapter = $sm->get(
					'User\Authentication\Adapter\OAuth2Adapter');
			
			$user = $e->getParam('user');
			$form = $e->getParam('form');
			
			$email = $form->get('email')->getValue();
			if ($email && ! $oAuth2Adapter->checkClientId($email)) {
				$apiKey = $user->addApiKey();
				$redirect_uri = '/oauth/receivecode';
				$grant_types = 'client_credentials refresh_token password jwt';
				
				$result = $oAuth2Adapter->setClientDetails($username, $apiKey, 
						$redirect_uri, $grant_types, null, $email);
			}
		}
		return $result;
	}
}