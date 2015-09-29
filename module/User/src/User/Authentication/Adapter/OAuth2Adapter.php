<?php
namespace User\Authentication\Adapter;
use ZF\OAuth2\Adapter\PdoAdapter;

/**
 * Custom extension of PdoAdapter to validate against the WEB_User table.
 */
class OAuth2Adapter extends PdoAdapter
{

	public function __construct ($connection, $config = array())
	{
		$config = [
				'user_table' => 'user'
		];
		
		return parent::__construct($connection, $config);
	}

	/**
	 * Check client user_id
	 *
	 * @param string $client_id        	
	 * @param string $client_secret        	
	 * @return bool
	 */
	public function checkClientId ($client_id)
	{
		$stmt = $this->db->prepare(
				sprintf('SELECT * from %s where client_id = :client_id', 
						$this->config['client_table']));
		$stmt->execute(compact('client_id'));
		$result = $stmt->fetch();
		
		return $result;
	}

	public function getUser ($username)
	{
		$sql = sprintf('SELECT * from %s where email=:username', 
				$this->config['user_table']);
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array(
				'username' => $username
		));
		
		if (! $userInfo = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			return false;
		}
		
		// the default behavior is to use "username" as the user_id
		return array_merge(array(
				'user_id' => $username
		), $userInfo);
	}

	public function setUser ($username, $password, $firstName = null, 
			$lastName = null)
	{
		// do not store in plaintext, use bcrypt
		$this->createBcryptHash($password);
		
		// if it exists, update it.
		if ($this->getUser($username)) {
			$sql = sprintf(
					'UPDATE %s SET password=:password WHERE email=:username', 
					$this->config['user_table']);
			$stmt = $this->db->prepare($sql);
		} else {
			$sql = sprintf(
					'INSERT INTO %s (email, password)
                    VALUES (:username, :password)', 
					$this->config['user_table']);
			$stmt = $this->db->prepare($sql);
		}
		
		return $stmt->execute(compact('username', 'password'));
	}

	protected function checkPassword ($user, $password)
	{
		return $this->verifyHash($password, $user['password']);
	}
}