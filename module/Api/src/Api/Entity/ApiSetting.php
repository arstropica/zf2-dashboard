<?php
namespace Api\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 *
 * @author arstropica
 *        
 *         @ORM\Table(name="api_settings")
 *         @ORM\Entity(repositoryClass="Api\Entity\Repository\ApiRepository")
 *         @Annotation\Instance("\Api\Entity\Api")
 */
class ApiSetting
{

	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 *
	 * @var \Api\Entity\ApiOption @ORM\ManyToOne(
	 *      targetEntity="Api\Entity\ApiOption",
	 *      fetch="EXTRA_LAZY",
	 *      cascade={"persist"},
	 *      )
	 *      @ORM\JoinColumn(
	 *      name="api_setting",
	 *      referencedColumnName="id",
	 *      nullable=false,
	 *      )
	 */
	private $apiOption;

	/**
	 *
	 * @var string @ORM\Column(name="api_value", type="string", length=255,
	 *      nullable=false)
	 */
	private $apiValue;

	/**
	 *
	 * @var \Account\Entity\Account @ORM\ManyToOne(
	 *      targetEntity="Account\Entity\Account",
	 *      inversedBy="apiSettings",
	 *      cascade={"persist"},
	 *      fetch="EXTRA_LAZY",
	 *      )
	 *      @ORM\JoinColumns(
	 *      {
	 *      @ORM\JoinColumn(
	 *      name="account_id",
	 *      referencedColumnName="id",
	 *      nullable=false,
	 *      )
	 *      }
	 *      )
	 */
	private $account;

	/**
	 *
	 * @var \Api\Entity\Api @ORM\ManyToOne(
	 *      targetEntity="Api\Entity\Api",
	 *      inversedBy="settings",
	 *      fetch="EXTRA_LAZY",
	 *      cascade={"merge", "persist"},
	 *      )
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(
	 *      name="api_id",
	 *      referencedColumnName="id",
	 *      nullable=false,
	 *      )
	 *      })
	 */
	private $api;

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId ()
	{
		return $this->id;
	}

	/**
	 * Set apiOption
	 *
	 * @param string $apiOption        	
	 *
	 * @return ApiSetting
	 */
	public function setApiOption ($apiOption)
	{
		$this->apiOption = $apiOption;
		
		return $this;
	}

	/**
	 * Get apiOption
	 *
	 * @return \Api\Entity\ApiOption
	 */
	public function getApiOption ()
	{
		return $this->apiOption;
	}

	/**
	 * Set apiValue
	 *
	 * @param string $apiValue        	
	 *
	 * @return ApiSetting
	 */
	public function setApiValue ($apiValue)
	{
		$this->apiValue = $apiValue;
		
		return $this;
	}

	/**
	 * Get apiValue
	 *
	 * @return string
	 */
	public function getApiValue ()
	{
		return $this->apiValue;
	}

	/**
	 * Set account
	 *
	 * @param \Account\Entity\Account $account        	
	 *
	 * @return ApiSetting
	 */
	public function setAccount (\Account\Entity\Account $account = null)
	{
		$this->account = $account;
		
		return $this;
	}

	/**
	 * Get account
	 *
	 * @return \Account\Entity\Account
	 */
	public function getAccount ()
	{
		return $this->account;
	}

	/**
	 * Set api
	 *
	 * @param \Api\Entity\Api $api        	
	 *
	 * @return ApiSetting
	 */
	public function setApi (\Api\Entity\Api $api = null)
	{
		$this->api = $api;
		
		return $this;
	}

	/**
	 * Get api
	 *
	 * @return \Api\Entity\Api
	 */
	public function getApi ()
	{
		return $this->api;
	}

	public function __toString ()
	{
		return $this->getApiOption();
	}
}

?>