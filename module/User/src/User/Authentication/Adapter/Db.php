<?php
namespace User\Authentication\Adapter;
use Zend\Authentication\Result as AuthenticationResult;
use ZfcUser\Authentication\Adapter\Db as ParentAdapter;
use ZfcUser\Authentication\Adapter\AdapterChainEvent as AuthEvent;

class Db extends ParentAdapter
{

	public function authenticate (AuthEvent $e)
	{
		$mapper = $this->getServiceManager()->get('User_user_mapper');
		$this->setMapper($mapper);
		
		if ($this->isSatisfied()) {
			$storage = $this->getStorage()->read();
			$e->setIdentity($storage['identity'])
				->setCode(AuthenticationResult::SUCCESS)
				->setMessages(array(
					'Authentication successful.'
			));
			
			return;
		}
		
		$identity = $e->getRequest()
			->getPost()
			->get('identity');
		$credential = $e->getRequest()
			->getPost()
			->get('credential');
		$credential = $this->preProcessCredential($credential);
		$userObject = null;
		
		// Cycle through the configured identity sources and test each
		$fields = $this->getOptions()->getAuthIdentityFields();
		while (! is_object($userObject) && count($fields) > 0) {
			$mode = array_shift($fields);
			switch ($mode) {
				case 'apiKey':
					$userObject = $this->getMapper()->findByApiKey($identity);
					break;
			}
		}

		if (! $userObject) {
			$e->setCode(AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND)->setMessages(
					array(
							'A record with the supplied identity could not be found.'
					));
			$this->setSatisfied(false);
			return false;
		}
		
		if ($this->getOptions()->getEnableUserState()) {
			// Don't allow user to login if state is not in allowed list
			if (! in_array($userObject->getState(), 
					$this->getOptions()->getAllowedLoginStates())) {
				$e->setCode(AuthenticationResult::FAILURE_UNCATEGORIZED)->setMessages(
						array(
								'A record with the supplied identity is not active.'
						));
				$this->setSatisfied(false);
				return false;
			}
		}
		
		// Success!
		$e->setIdentity($userObject->getId());
		$this->setSatisfied(true);
		$storage = $this->getStorage()->read();
		$storage['identity'] = $e->getIdentity();
		$this->getStorage()->write($storage);
		$e->setCode(AuthenticationResult::SUCCESS)->setMessages(
				array(
						'Authentication successful.'
				));
	}
}