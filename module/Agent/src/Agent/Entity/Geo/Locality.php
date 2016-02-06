<?php

namespace Agent\Entity\Geo;

use JMS\Serializer\Annotation as JMS;
use Doctrine\Search\Mapping\Annotations as MAP;
use Application\Provider\SearchManagerAwareTrait;
use Application\Service\ElasticSearch\SearchableEntityInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\EntityDataTrait;

/**
 * Locality
 *
 * @JMS\ExclusionPolicy("all")
 * @MAP\ElasticSearchable(
 * index="usgeodb",
 * type="locality",
 * source=true
 * )
 */
class Locality implements SearchableEntityInterface, ServiceLocatorAwareInterface {
	use EntityDataTrait, SearchManagerAwareTrait, ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var integer @MAP\Id
	 *      @JMS\Type("integer")
	 *      @JMS\Expose @JMS\Groups({"geo"})
	 */
	private $id;
	
	/**
	 *
	 * @var string @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"geo"})
	 *      @MAP\ElasticField(
	 *      type="string",
	 *      includeInAll=true
	 *      )
	 */
	private $city;
	
	/**
	 *
	 * @var string @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"geo"})
	 *      @MAP\ElasticField(
	 *      type="string",
	 *      includeInAll=true
	 *      )
	 */
	private $county;
	
	/**
	 *
	 * @var string @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"geo"})
	 *      @MAP\ElasticField(
	 *      type="string",
	 *      includeInAll=true
	 *      )
	 */
	private $countyfips;
	
	/**
	 *
	 * @var float[] @JMS\Type("array<double>")
	 *      @JMS\Expose @JMS\Groups({"geo"})
	 *      @MAP\ElasticField(name="latlon", type="double")
	 */
	private $latlon;
	
	/**
	 *
	 * @var float[] @JMS\Type("array<string, double>")
	 *      @JMS\Expose @JMS\Groups({"geo"})
	 *      @MAP\ElasticField(name="location", type="nested", properties={
	 *      @MAP\ElasticField(name="longitude", type="double"),
	 *      @MAP\ElasticField(name="latitude", type="double")
	 *      })
	 */
	private $location;
	
	/**
	 *
	 * @var string @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"geo"})
	 *      @MAP\ElasticField(
	 *      type="string",
	 *      includeInAll=true
	 *      )
	 */
	private $preference;
	
	/**
	 *
	 * @var array @JMS\Type("array<string, string>")
	 *      @JMS\Expose @JMS\Groups({"geo"})
	 *      @MAP\ElasticField(name="state", type="nested", properties={
	 *      @MAP\ElasticField(name="abbrev", type="string", includeInAll=true,
	 *      analyzer="analyzer_keyword"),
	 *      @MAP\ElasticField(name="full", type="string", includeInAll=true,
	 *      analyzer="analyzer_keyword")
	 *      })
	 */
	private $state;
	
	/**
	 *
	 * @var string @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"geo"})
	 *      @MAP\ElasticField(
	 *      type="string",
	 *      includeInAll=true
	 *      )
	 */
	private $statefips;
	
	/**
	 *
	 * @var string @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"geo"})
	 *      @MAP\ElasticField(
	 *      type="string",
	 *      includeInAll=true
	 *      )
	 */
	private $type;
	
	/**
	 *
	 * @var string @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"geo"})
	 *      @MAP\ElasticField(
	 *      type="string",
	 *      includeInAll=true
	 *      )
	 */
	private $zip;

	/**
	 *
	 * @return integer $id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 *
	 * @return string $city
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 *
	 * @return string $county
	 */
	public function getCounty()
	{
		return $this->county;
	}

	/**
	 *
	 * @return string $countyfips
	 */
	public function getCountyfips()
	{
		return $this->countyfips;
	}

	/**
	 *
	 * @return float[] $latlon
	 */
	public function getLatlon()
	{
		return $this->latlon;
	}

	/**
	 *
	 * @return float[] $location
	 */
	public function getLocation()
	{
		return $this->location;
	}

	/**
	 *
	 * @return string $preference
	 */
	public function getPreference()
	{
		return $this->preference;
	}

	/**
	 *
	 * @return array $state
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 *
	 * @return string $statefips
	 */
	public function getStatefips()
	{
		return $this->statefips;
	}

	/**
	 *
	 * @return string $type
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 *
	 * @return string $zip
	 */
	public function getZip()
	{
		return $this->zip;
	}

	/**
	 *
	 * @param integer $id        	
	 *
	 * @return Locality
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 *
	 * @param string $city        	
	 *
	 * @return Locality
	 */
	public function setCity($city)
	{
		$this->city = $city;
		return $this;
	}

	/**
	 *
	 * @param string $county        	
	 *
	 * @return Locality
	 */
	public function setCounty($county)
	{
		$this->county = $county;
		return $this;
	}

	/**
	 *
	 * @param string $countyfips        	
	 *
	 * @return Locality
	 */
	public function setCountyfips($countyfips)
	{
		$this->countyfips = $countyfips;
		return $this;
	}

	/**
	 *
	 * @param float[] $latlon        	
	 *
	 * @return Locality
	 */
	public function setLatlon($latlon)
	{
		$this->latlon = $latlon;
		return $this;
	}

	/**
	 *
	 * @param float[] $location        	
	 *
	 * @return Locality
	 */
	public function setLocation($location)
	{
		$this->location = $location;
		return $this;
	}

	/**
	 *
	 * @param string $preference        	
	 *
	 * @return Locality
	 */
	public function setPreference($preference)
	{
		$this->preference = $preference;
		return $this;
	}

	/**
	 *
	 * @param array $state        	
	 *
	 * @return Locality
	 */
	public function setState($state)
	{
		$this->state = $state;
		return $this;
	}

	/**
	 *
	 * @param string $statefips        	
	 *
	 * @return Locality
	 */
	public function setStatefips($statefips)
	{
		$this->statefips = $statefips;
		return $this;
	}

	/**
	 *
	 * @param string $type        	
	 *
	 * @return Locality
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 *
	 * @param string $zip        	
	 *
	 * @return Locality
	 */
	public function setZip($zip)
	{
		$this->zip = $zip;
		return $this;
	}

}

?>