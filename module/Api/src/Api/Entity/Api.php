<?php
namespace Api\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Api
 *
 * @ORM\Table(name="api")
 * @ORM\Entity
 */
class Api
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
	 * @var string @ORM\Column(name="name", type="string", length=255,
	 *      nullable=false)
	 */
	private $name;

	/**
	 *
	 * @var string @ORM\Column(name="description", type="text", length=65535,
	 *      nullable=true)
	 */
	private $description;

	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection @ORM\OneToMany(
	 *      targetEntity="Api\Entity\ApiSetting",
	 *      mappedBy="api",
	 *      fetch="EXTRA_LAZY",
	 *      indexBy="id",
	 *      cascade={"all","merge","persist","refresh","remove"}
	 *      )
	 *     
	 */
	private $settings;

	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection @ORM\OneToMany(
	 *      targetEntity="Api\Entity\ApiOption",
	 *      mappedBy="api",
	 *      fetch="EXTRA_LAZY"
	 *      )
	 *     
	 */
	private $options;

	/**
	 * Initialies the collection variables.
	 */
	public function __construct ()
	{
		$this->settings = new ArrayCollection();
		$this->options = new ArrayCollection();
	}

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
	 * Set name
	 *
	 * @param string $name        	
	 *
	 * @return Api
	 */
	public function setName ($name)
	{
		$this->name = $name;
		
		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName ()
	{
		return $this->name;
	}

	/**
	 * Set description
	 *
	 * @param string $description        	
	 *
	 * @return Api
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
	 * Get settings.
	 *
	 * @return array
	 */
	public function getSettings ()
	{
		return $this->settings->getValues();
	}

	/**
	 * Add a setting to the api.
	 *
	 * @param \Api\Entity\ApiSetting $setting        	
	 *
	 * @return void
	 */
	public function addSetting (\Api\Entity\ApiSetting $setting)
	{
		$setting->setApi($this);
		$this->settings[] = $setting;
	}

	/**
	 *
	 * @param string $optionName        	
	 *
	 * @return \Api\Entity\ApiOption|boolean
	 */
	public function findOption ($optionName)
	{
		$option = false;
		$options = $this->findOptions($optionName);
		if ($options->count() > 0) {
			$option = $options->first();
		}
		return $option;
	}

	/**
	 *
	 * @param string $optionName        	
	 *
	 * @return ArrayCollection
	 */
	public function findOptions ($optionName)
	{
		return $this->getOptions(true)->filter(function  ($option) use( $optionName)
		{
			return $option->getOption() == $optionName;
		});
	}

	/**
	 * Get options.
	 *
	 * @return array
	 */
	public function getOptions ($ac = false)
	{
		return $ac ? $this->options : $this->options->getValues();
	}

	/**
	 * Add options to api.
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $options        	
	 *
	 * @return void
	 */
	public function addOptions (ArrayCollection $options)
	{
		foreach ($options as $option) {
			if (! $this->options->contains($option)) {
				$this->option->add($option);
				$option->setApi($this);
			}
		}
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\ArrayCollection $options        	
	 *
	 * @return Api
	 */
	public function removeOptions (ArrayCollection $options)
	{
		foreach ($options as $option) {
			if ($this->options->contains($option)) {
				$this->options->removeElement($options);
				$option->setApi(null);
			}
		}
		
		return $this;
	}

	public function __toString ()
	{
		return $this->getName();
	}
}
