<?php
namespace Lead\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\Collection;
use Zend\Db\Sql\Ddl\Column\Integer;

/**
 * LeadAttribute
 *
 * @ORM\Table(name="lead_attributes")
 * @ORM\Entity(repositoryClass="Lead\Entity\Repository\LeadAttributeRepository")
 * @Annotation\Instance("\Lead\Entity\LeadAttribute")
 */
class LeadAttribute
{

	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 *      @Annotation\Exclude()
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
	 */
	private $attributeDesc;

	/**
	 *
	 * @var integer @ORM\Column(name="attribute_order", type="integer",
	 *      nullable=true)
	 *      @Annotation\Exclude()
	 */
	private $attributeOrder;

	/**
	 *
	 * @var \Doctrine\Common\Collections\Collection
	 *      @ORM\OneToMany(targetEntity="Lead\Entity\LeadAttributeValue",
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
	public function __construct ()
	{
		$this->values = new ArrayCollection();
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

	public function setId ($id)
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
	public function setAttributeName ($attributeName)
	{
		$this->attributeName = $attributeName;
		
		return $this;
	}

	/**
	 * Get attributeName
	 *
	 * @return string
	 */
	public function getAttributeName ()
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
	public function setAttributeDesc ($attributeDesc)
	{
		$this->attributeDesc = $attributeDesc;
		
		return $this;
	}

	/**
	 * Get attributeDesc
	 *
	 * @return string
	 */
	public function getAttributeDesc ()
	{
		return $this->attributeDesc;
	}

	/**
	 * Get attributeOrder
	 *
	 * @return integer|null
	 */
	public function getAttributeOrder ()
	{
		return $this->attributeOrder;
	}

	/**
	 *
	 * @param integer $attributeOrder        	
	 *
	 * @return LeadAttribute
	 */
	public function setAttributeOrder ($attributeOrder)
	{
		$this->attributeOrder = $attributeOrder;
		
		return $this;
	}

	/**
	 * Get values.
	 *
	 * @return array
	 */
	public function getValues ($ac = false)
	{
		return $ac ? $this->values : $this->values->getValues();
	}

	/**
	 * Add a value to the attribute.
	 *
	 * @param \Lead\Entity\LeadAttributeValue $value        	
	 *
	 * @return void
	 */
	public function addValue ($value)
	{
		$value->setAttribute($this);
		$this->values[] = $value;
	}

	/**
	 * Add values to attribute.
	 *
	 * @param Collection $values        	
	 *
	 * @return void
	 */
	public function addValues (Collection $values)
	{
		foreach ($values as $value) {
			if (! $this->values->contains($value)) {
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
	public function setValues ($values)
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
	public function removeValues (Collection $values)
	{
		foreach ($values as $value) {
			if ($this->values->contains($value)) {
				$this->values->removeElement($value);
				$value->setAttribute(null);
			}
		}
		
		return $this;
	}

	/**
	 * Get values count
	 *
	 * @return integer
	 */
	public function getCount ()
	{
		return $this->getValues(true)->count();
	}

	/**
	 * Get string equivalent.
	 *
	 * @return string
	 */
	public function __toString ()
	{
		return $this->getAttributeOrder();
	}
}
