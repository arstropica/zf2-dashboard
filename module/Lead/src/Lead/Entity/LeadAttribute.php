<?php

namespace Lead\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\Collection;
use Zend\Db\Sql\Ddl\Column\Integer;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Search\Mapping\Annotations as MAP;
use Application\Provider\EntityDataTrait;
use Application\Provider\SearchManagerAwareTrait;
use Application\Service\ElasticSearch\SearchableEntityInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * LeadAttribute
 *
 * @ORM\Table(name="lead_attributes")
 * @ORM\Entity(repositoryClass="Lead\Entity\Repository\LeadAttributeRepository")
 * @Annotation\Instance("\Lead\Entity\LeadAttribute")
 * @JMS\ExclusionPolicy("all")
 * @MAP\ElasticSearchable(
 * index="reports",
 * type="attribute",
 * source=true
 * )
 */
class LeadAttribute implements SearchableEntityInterface, ServiceLocatorAwareInterface {
	use EntityDataTrait, SearchManagerAwareTrait, ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 *      @Annotation\Exclude()
	 *      @MAP\Id
	 *      @JMS\Type("integer")
	 *      @JMS\Expose @JMS\Groups({"details", "attributes"})
	 */
	private $id;
	
	/**
	 *
	 * @var string @ORM\Column(name="attribute_name", type="string", length=255,
	 *      nullable=false)
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 *      @Annotation\Filter({"name":"StripTags"})
	 *      @Annotation\Filter({"name":"StringTrim"})
	 *      @Annotation\Required(true)
	 *      @Annotation\Options({
	 *      "required":"true",
	 *      "label":"Name",
	 *      })
	 *      @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"details", "attributes"})
	 *      @MAP\ElasticField(
	 *      type="string",
	 *      includeInAll=true
	 *      )
	 */
	private $attributeName;
	
	/**
	 *
	 * @var string @ORM\Column(name="attribute_desc", type="text", length=65535,
	 *      nullable=false)
	 *      @Annotation\Filter({"name":"StripTags"})
	 *      @Annotation\Filter({"name":"StringTrim"})
	 *      @Annotation\Required(true)
	 *      @Annotation\Options({
	 *      "required":"true",
	 *      "label":"Description",
	 *      })
	 *      @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"details", "attributes"})
	 *      @MAP\ElasticField(type="multi_field", fields={
	 *      @MAP\ElasticField(name="attributeDesc", type="string",
	 *      includeInAll=true, analyzer="whitespace"),
	 *      @MAP\ElasticField(name="exact", type="string",
	 *      includeInAll=false, index="not_analyzed")
	 *      })
	 */
	private $attributeDesc;
	
	/**
	 *
	 * @var string @ORM\Column(name="attribute_type", type="text", length=55,
	 *      nullable=false)
	 *      @Annotation\Filter({"name":"StripTags"})
	 *      @Annotation\Filter({"name":"StringTrim"})
	 *      @Annotation\Required(true)
	 *      @Annotation\Type("Zend\Form\Element\Select")
	 *      @Annotation\Options({
	 *      "required":"true",
	 *      "label":"Type",
	 *      "empty_option": "Select Data Type",
	 *      "value_options": {
	 *      "number":"Number",
	 *      "date":"Date",
	 *      "string":"String",
	 *      "text":"Text",
	 *      "multiple":"Multiple",
	 *      "boolean":"Boolean",
	 *      "location":"Location"
	 *      }
	 *      })
	 *      @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"details", "attributes"})
	 *      @MAP\ElasticField(
	 *      type="string",
	 *      includeInAll=true
	 *      )
	 */
	private $attributeType = 'string';
	
	/**
	 *
	 * @var integer @ORM\Column(name="attribute_order", type="integer",
	 *      nullable=true)
	 *      @Annotation\Exclude()
	 *      @JMS\Type("integer")
	 *      @JMS\Expose @JMS\Groups({"details", "attributes"})
	 *      @MAP\ElasticField(
	 *      type="string",
	 *      includeInAll=true
	 *      )
	 */
	private $attributeOrder;
	
	/**
	 *
	 * @var integer @ORM\Column(name="active", type="integer", nullable=false)
	 *      @Annotation\Exclude()
	 *      @JMS\Type("integer")
	 *      @JMS\Expose @JMS\Groups({"list", "attributes"})
	 *      @MAP\ElasticField(
	 *      type="integer",
	 *      nullValue="1",
	 *      includeInAll=true
	 *      )
	 */
	private $active;
	
	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection @ORM\OneToMany(targetEntity="Lead\Entity\LeadAttributeValue",
	 *      mappedBy="attribute", cascade={"persist", "remove"},
	 *      fetch="EXTRA_LAZY")
	 *      @Annotation\Exclude()
	 */
	protected $values;
	
	/**
	 *
	 * @var Integer @Annotation\Exclude()
	 */
	protected $count;

	/**
	 * Initialies the array variables.
	 */
	public function __construct()
	{
		$this->values = new ArrayCollection();
		$this->active = 1;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	public function setId($id)
	{
		$this->id = $id;
		
		return $this;
	}

	/**
	 * Set attributeName
	 *
	 * @param string $attributeName        	
	 *
	 * @return LeadAttribute
	 */
	public function setAttributeName($attributeName)
	{
		$this->attributeName = $attributeName;
		
		return $this;
	}

	/**
	 * Get attributeName
	 *
	 * @return string
	 */
	public function getAttributeName()
	{
		return $this->attributeName;
	}

	/**
	 * Set attributeDesc
	 *
	 * @param string $attributeDesc        	
	 *
	 * @return LeadAttribute
	 */
	public function setAttributeDesc($attributeDesc)
	{
		$this->attributeDesc = $attributeDesc;
		
		return $this;
	}

	/**
	 * Get attributeDesc
	 *
	 * @return string
	 */
	public function getAttributeDesc()
	{
		return $this->attributeDesc;
	}

	/**
	 * Get Attribute Type
	 *
	 * @return string $attributeType
	 */
	public function getAttributeType()
	{
		return $this->attributeType;
	}

	/**
	 * Set Attribute Type
	 *
	 * @param string $attributeType        	
	 *
	 * @return LeadAttribute
	 */
	public function setAttributeType($attributeType)
	{
		$this->attributeType = $attributeType;
		
		return $this;
	}

	/**
	 * Get attributeOrder
	 *
	 * @return integer|null
	 */
	public function getAttributeOrder()
	{
		return $this->attributeOrder;
	}

	/**
	 *
	 * @param integer $attributeOrder        	
	 *
	 * @return LeadAttribute
	 */
	public function setAttributeOrder($attributeOrder)
	{
		$this->attributeOrder = $attributeOrder;
		
		return $this;
	}

	/**
	 * Get values.
	 *
	 * @return array
	 */
	public function getValues($ac = false)
	{
		$values = $this->values->filter(function($value) {
		    try {
		      $lead = $value ? $value->getLead() : false;
		      return $lead ? $lead->getActive() : false;
		    } catch (\Exception $e) {
		        return false;
		    }
		});
		return $ac ? $values : $values->getValues();
	}

	/**
	 * Add a value to the attribute.
	 *
	 * @param \Lead\Entity\LeadAttributeValue $value        	
	 *
	 * @return void
	 */
	public function addValue($value)
	{
		$value->setAttribute($this);
		$this->values [] = $value;
	}

	/**
	 * Add values to attribute.
	 *
	 * @param Collection $values        	
	 *
	 * @return void
	 */
	public function addValues(Collection $values)
	{
		foreach ( $values as $value ) {
			if (!$this->values->contains($value)) {
				$this->values->add($value);
				$value->setAttribute($this);
			}
		}
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\Collection $values        	
	 *
	 * @return LeadAttribute
	 */
	public function setValues($values)
	{
		$this->values = $values;
		
		return $this;
	}

	/**
	 *
	 * @param Collection $values        	
	 *
	 * @return LeadAttribute
	 */
	public function removeValues(Collection $values)
	{
		foreach ( $values as $value ) {
			if ($this->values->contains($value)) {
				$this->values->removeElement($value);
				$value->setAttribute(null);
			}
		}
		
		return $this;
	}

	/**
	 * Get active
	 *
	 * @return integer $active
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * Set active
	 *
	 * @param integer $active        	
	 *
	 * @return LeadAttribute
	 */
	public function setActive($active)
	{
	    $this->active = $active;
	    return $this;
	}
	
	/**
	 * Get values count
	 *
	 * @return integer
	 */
	public function getCount()
	{
		return $this->getValues(true)
			->count();
	}

	/**
	 * Get string equivalent.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getAttributeOrder();
	}
}
