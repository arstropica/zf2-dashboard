<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
namespace User\Entity;
use BjyAuthorize\Provider\Role\ProviderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ZfcUser\Entity\UserInterface;

/**
 * An example of how to implement a role aware user entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="user")
 *
 * @author Tom Oram <tom@scl.co.uk>
 */
class User implements UserInterface, ProviderInterface
{

	/**
	 *
	 * @var int @ORM\Id
	 *      @ORM\Column(type="integer")
	 *      @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 *
	 * @var string @ORM\Column(type="string", length=255, unique=true,
	 *      nullable=true)
	 */
	protected $username;

	/**
	 *
	 * @var string @ORM\Column(type="string", unique=true, length=255)
	 */
	protected $email;

	/**
	 *
	 * @var string @ORM\Column(type="string", length=50, nullable=true)
	 */
	protected $displayName;

	/**
	 *
	 * @var string @ORM\Column(type="string", length=50, nullable=true)
	 */
	protected $apiKey;

	/**
	 *
	 * @var string @ORM\Column(type="string", length=128)
	 */
	protected $password;

	/**
	 *
	 * @var int
	 */
	protected $state;

	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection
	 *      @ORM\ManyToMany(targetEntity="User\Entity\Role")
	 *      @ORM\JoinTable(name="user_role_linker",
	 *      joinColumns={@ORM\JoinColumn(name="user_id",
	 *      referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="role_id",
	 *      referencedColumnName="id")}
	 *      )
	 */
	protected $roles;

	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection
	 *      @ORM\ManyToMany(targetEntity="Account\Entity\Account")
	 *      @ORM\JoinTable(name="user_account_linker",
	 *      joinColumns={@ORM\JoinColumn(name="user_id",
	 *      referencedColumnName="id")},
	 *      inverseJoinColumns={
	 *      @ORM\JoinColumn(name="account_id", referencedColumnName="id"),
	 *      }
	 *      )
	 */
	protected $accounts;

	/**
	 * Initialies the roles & accounts variables.
	 */
	public function __construct ()
	{
		$this->roles = new ArrayCollection();
		$this->accounts = new ArrayCollection();
	}

	/**
	 * Get id.
	 *
	 * @return int
	 */
	public function getId ()
	{
		return $this->id;
	}

	/**
	 * Set id.
	 *
	 * @param int $id        	
	 *
	 * @return void
	 */
	public function setId ($id)
	{
		$this->id = (int) $id;
	}

	/**
	 * Get username.
	 *
	 * @return string
	 */
	public function getUsername ()
	{
		return $this->username;
	}

	/**
	 * Set username.
	 *
	 * @param string $username        	
	 *
	 * @return void
	 */
	public function setUsername ($username)
	{
		$this->username = $username;
	}

	/**
	 * Get email.
	 *
	 * @return string
	 */
	public function getEmail ()
	{
		return $this->email;
	}

	/**
	 * Set email.
	 *
	 * @param string $email        	
	 *
	 * @return void
	 */
	public function setEmail ($email)
	{
		$this->email = $email;
	}

	/**
	 * Get displayName.
	 *
	 * @return string
	 */
	public function getDisplayName ()
	{
		return $this->displayName;
	}

	/**
	 * Set displayName.
	 *
	 * @param string $displayName        	
	 *
	 * @return void
	 */
	public function setDisplayName ($displayName)
	{
		$this->displayName = $displayName;
	}

	/**
	 *
	 * @return the $apiKey
	 */
	public function getApiKey ()
	{
		return $this->apiKey;
	}

	/**
	 *
	 * @param \Application\Entity\string $apiKey        	
	 */
	public function setApiKey ($apiKey)
	{
		$this->apiKey = $apiKey;
	}

	/**
	 * Get password.
	 *
	 * @return string
	 */
	public function getPassword ()
	{
		return $this->password;
	}

	/**
	 * Set password.
	 *
	 * @param string $password        	
	 *
	 * @return void
	 */
	public function setPassword ($password)
	{
		$this->password = $password;
	}

	/**
	 * Get state.
	 *
	 * @return int
	 */
	public function getState ()
	{
		return $this->state;
	}

	/**
	 * Set state.
	 *
	 * @param int $state        	
	 *
	 * @return void
	 */
	public function setState ($state)
	{
		$this->state = $state;
	}

	/**
	 * Get roles.
	 *
	 * @return array
	 */
	public function getRoles ()
	{
		return $this->roles->getValues();
	}

	/**
	 * Get role.
	 *
	 * @return string
	 */
	public function getRole ()
	{
		$roles = $this->getRoles();
		if ($roles) {
			return $roles[0]->getRoleId();
		}
		return 'guest';
	}

	/**
	 * Add a role to the user.
	 *
	 * @param Role $role        	
	 *
	 * @return void
	 */
	public function addRole ($role)
	{
		$this->roles[] = $role;
	}

	/**
	 * Get account.
	 *
	 * @return array
	 */
	public function getAccounts ()
	{
		return $this->accounts->getValues();
	}

	/**
	 * Add a account to the user.
	 *
	 * @param \Account\Entity\Account $account        	
	 *
	 * @return void
	 */
	public function addAccount ($account)
	{
		$this->accounts[] = $account;
	}

	/**
	 * Generate API Key
	 *
	 * @param integer $len        	
	 * @param boolean $readable        	
	 * @param boolean $hash        	
	 *
	 * @return string
	 */
	public function addApiKey ($len = 16, $readable = true, $hash = false)
	{
		$key = '';
		
		if ($hash)
			$key = substr(sha1(uniqid(rand(), true)), 0, $len);
		else 
			if ($readable) {
				$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
				
				for ($i = 0; $i < $len; ++ $i)
					$key .= substr($chars, (mt_rand() % strlen($chars)), 1);
			} else
				for ($i = 0; $i < $len; ++ $i)
					$key .= chr(mt_rand(33, 126));
		
		$this->setApiKey($key);
		return $key;
	}
}
