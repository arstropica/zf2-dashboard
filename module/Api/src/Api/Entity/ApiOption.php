<?php
namespace Api\Entity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 *
 * @author arstropica
 *        
 *         @ORM\Table(name="api_options")
 *         @ORM\Entity
 *         @Annotation\Instance("\Api\Entity\ApiOption")
 */
class ApiOption
{

	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $id;

	/**
	 *
	 * @var string @ORM\Column(name="option", type="string", length=255,
	 *      nullable=false)
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $option;

	/**
	 *
	 * @var string @ORM\Column(name="value", type="string", length=255,
	 *      nullable=true)
	 *      @Annotation\Type("Zend\Form\Element\Text")
	 *      @Annotation\Required({"required":"true"})
	 *      @Annotation\Filter({"name":"StripTags"})
	 *      @Annotation\Options({"label":"Value"})
	 */
	private $value;

	/**
	 *
	 * @var string @ORM\Column(name="description", type="text", length=65535,
	 *      nullable=true)
	 *      @Annotation\Type("Zend\Form\Element\Textarea")
	 *      @Annotation\Options({"label":"Description"})
	 *      @Annotation\Attributes({"readonly":"readonly"})
	 *      @Annotation\Filter({"name":"StringTrim"})
	 */
	private $description;

	/**
	 *
	 * @var string @ORM\Column(name="label", type="string", length=45,
	 *      nullable=false)
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $label;

	/**
	 *
	 * @var string @ORM\Column(name="scope", type="string", length=45,
	 *      nullable=false)
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $scope;

	/**
	 *
	 * @var \Api\Entity\Api @ORM\ManyToOne(
	 *      targetEntity="Api\Entity\Api",
	 *      inversedBy="options",
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
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
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
	 * Set option
	 *
	 * @param string $option        	
	 *
	 * @return ApiOption
	 */
	public function setOption ($option)
	{
		$this->option = $option;
		
		return $this;
	}

	/**
	 * Get option
	 *
	 * @return string
	 */
	public function getOption ()
	{
		return $this->option;
	}

	/**
	 * Set value
	 *
	 * @param string $value        	
	 *
	 * @return ApiOption
	 */
	public function setValue ($value)
	{
		$this->value = $value;
		
		return $this;
	}

	/**
	 * Get value
	 *
	 * @return string
	 */
	public function getValue ()
	{
		return $this->value;
	}

	/**
	 * Set description
	 *
	 * @param string $description        	
	 *
	 * @return ApiOption
	 */
	public function setDescription ($description)
	{
		$this->description = $description;
		
		return $this;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription ()
	{
		return $this->description;
	}

	/**
	 * Set label
	 *
	 * @param string $label        	
	 *
	 * @return ApiOption
	 */
	public function setLabel ($label)
	{
		$this->label = $label;
		
		return $this;
	}

	/**
	 * Get label
	 *
	 * @return string
	 */
	public function getLabel ()
	{
		return $this->label;
	}

	/**
	 * Set scope
	 *
	 * @param string $scope        	
	 *
	 * @return ApiOption
	 */
	public function setScope ($scope)
	{
		$this->scope = $scope;
		
		return $this;
	}

	/**
	 * Get scope
	 *
	 * @return string
	 */
	public function getScope ()
	{
		return $this->scope;
	}

	/**
	 * Set api
	 *
	 * @param \Api\Entity\Api $api        	
	 *
	 * @return ApiOption
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
		return $this->getOption();
	}
}
