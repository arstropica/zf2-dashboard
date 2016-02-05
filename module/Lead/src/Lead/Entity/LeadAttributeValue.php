<?php

namespace Lead\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use JMS\Serializer\Annotation\MaxDepth;
use Doctrine\Search\Mapping\Annotations as MAP;
use Application\Provider\EntityDataTrait;
use Application\Provider\SearchManagerAwareTrait;
use Application\Service\ElasticSearch\SearchableEntityInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Utility\Helper;

/**
 * LeadAttributeValue
 *
 * @ORM\Table(name="lead_attribute_values",
 * indexes={@ORM\Index(name="idx_lead_id", columns={"lead_id"}),
 * @ORM\Index(name="idx_attribute_id", columns={"attribute_id"})})
 * @ORM\Entity(repositoryClass="Lead\Entity\Repository\LeadAttributeValueRepository")
 * @JMS\ExclusionPolicy("all")
 * @MAP\ElasticSearchable(
 * index="reports",
 * type="value",
 * source=true,
 * parent="lead"
 * )
 * @MAP\ElasticRoot(name="dynamic_templates", id="template_1", match="value*",
 * mapping={
 * @MAP\ElasticField(type="multi_field", fields={
 * @MAP\ElasticField(name="{name}", type="string", includeInAll=false),
 * @MAP\ElasticField(name="untouched", type="string", index="not_analyzed")
 * })
 * })
 * @MAP\ElasticRoot(name="date_detection", value="false")
 */
class LeadAttributeValue implements SearchableEntityInterface, ServiceLocatorAwareInterface {
	use EntityDataTrait, SearchManagerAwareTrait, ServiceLocatorAwareTrait;
	
	/**
	 *
	 * @var integer @ORM\Column(name="id", type="integer", nullable=false)
	 *      @ORM\Id
	 *      @ORM\GeneratedValue(strategy="IDENTITY")
	 *      @MAP\Id
	 *      @JMS\Type("integer")
	 *      @JMS\Expose @JMS\Groups({"details", "attributes"})
	 */
	private $id;
	
	/**
	 *
	 * @var string @ORM\Column(name="value", type="text", length=65535,
	 *      nullable=true)
	 *      @JMS\Type("string")
	 *      @JMS\Expose @JMS\Groups({"details", "attributes"})
	 *      @MAP\ElasticField(name="value", type="multi_field", fields={
	 *      @MAP\ElasticField(name="value", type="string", includeInAll=true,
	 *      index="analyzed"),
	 *      @MAP\ElasticField(name="exact", type="string",
	 *      includeInAll=false, index="not_analyzed"),
	 *      })
	 *     
	 * @see dynamic template root mapping
	 */
	private $value;
	
	/**
	 *
	 * @var \DateTime @JMS\Type("DateTime")
	 *      @JMS\Expose @JMS\Groups({"details", "attributes"})
	 *      @MAP\ElasticField(name="_date", type="date",
	 *      nullValue=null)
	 *     
	 */
	private $_date;
	
	/**
	 *
	 * @var integer @JMS\Type("double")
	 *      @JMS\Expose @JMS\Groups({"details", "attributes"})
	 *      @MAP\ElasticField(name="_number", type="double",
	 *      nullValue=null)
	 *     
	 */
	private $_number;
	
	/**
	 *
	 * @var \Lead\Entity\LeadAttribute @ORM\ManyToOne(targetEntity="Lead\Entity\LeadAttribute",
	 *      inversedBy="values", cascade={"persist"})
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
	 *      })
	 *      @JMS\Type("Lead\Entity\LeadAttribute")
	 *      @JMS\Expose @JMS\Groups({"details", "attributes"})
	 *      @MaxDepth(1)
	 *      @MAP\ElasticField(name="attribute", type="nested", properties={
	 *      @MAP\ElasticField(name="id", type="integer", includeInAll=false,
	 *      index="not_analyzed"),
	 *      @MAP\ElasticField(name="attributeDesc", type="string",
	 *      includeInAll=true, index="not_analyzed")
	 *      })
	 */
	private $attribute;
	
	private $order;
	
	/**
	 *
	 * @var \Lead\Entity\Lead @ORM\ManyToOne(targetEntity="Lead\Entity\Lead",
	 *      inversedBy="attributes", cascade={"persist"})
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="lead_id", referencedColumnName="id")
	 *      })
	 */
	private $lead;
	
	/**
	 * @JMS\Type("integer")
	 * @JMS\Expose @JMS\Groups({"details", "attributes"})
	 * @MAP\Parameter(name="_parent")
	 */
	private $_parent;

	public function __construct()
	{

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

	/**
	 * Set value
	 *
	 * @param string $value        	
	 *
	 * @return LeadAttributeValue
	 */
	public function setValue($value)
	{
		$this->value = $value;
		
		return $this;
	}

	/**
	 * Get value
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 *
	 * @return \DateTime $_date
	 */
	public function getDate()
	{
		return $this->_date;
	}

	/**
	 *
	 * @param string $_date        	
	 *
	 * @return LeadAttributeValue
	 */
	public function setDate($_date)
	{
		$this->_date = $this->format_value($_date, 'date');
		return $this;
	}

	/**
	 *
	 * @return integer $_number
	 */
	public function getNumber()
	{
		return $this->_number;
	}

	/**
	 *
	 * @param integer $_number        	
	 */
	public function setNumber($_number)
	{
		$this->_number = $this->format_value($_number, 'number');
		;
		return $this;
	}

	/**
	 * Set attribute
	 *
	 * @param \Lead\Entity\LeadAttribute $attribute        	
	 *
	 * @return LeadAttributeValue
	 */
	public function setAttribute(\Lead\Entity\LeadAttribute $attribute = null)
	{
		$this->attribute = $attribute;
		
		return $this;
	}

	/**
	 * Get attribute
	 *
	 * @return \Lead\Entity\LeadAttribute
	 */
	public function getAttribute()
	{
		return $this->attribute;
	}

	/**
	 * Get Attribute Order
	 *
	 * @return integer
	 */
	public function getOrder()
	{
		$attribute = $this->attribute;
		if ($attribute) {
			return $attribute->getAttributeOrder();
		} else {
			return 0;
		}
	}

	/**
	 * Set Attribute Order
	 *
	 * @param integer $order        	
	 *
	 * @return LeadAttributeValue
	 */
	public function setOrder($order)
	{
		$this->attribute->setAttributeOrder($order);
		
		return $this;
	}

	/**
	 * Set lead
	 *
	 * @param \Lead\Entity\Lead $lead        	
	 *
	 * @return LeadAttributeValue
	 */
	public function setLead(\Lead\Entity\Lead $lead = null)
	{
		$this->lead = $lead;
		if ($this->lead instanceof Lead) {
			$this->setParent($this->lead->getId());
		}
		
		return $this;
	}

	/**
	 * Get lead
	 *
	 * @return \Lead\Entity\Lead
	 */
	public function getLead()
	{
		return $this->lead;
	}

	/**
	 * Set Parent
	 *
	 * @param string $_parent        	
	 *
	 * @return LeadAttributeValue
	 */
	public function setParent($_parent)
	{
		$this->_parent = $_parent;
		
		return $this;
	}

	/**
	 * Get Parent
	 *
	 * @return string $_parent
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	public function format_value($value, $type = null)
	{
		$formatted = null;
		if (!$type) {
			$attribute = $this->getAttribute();
			if (isset($attribute)) {
				$type = $attribute->getAttributeType() ?  : 'string';
			}
		}
		switch ($type) {
			case 'date' :
				$date = str_replace('-', '/', $this->value);
				if (count(explode('/', $date)) > 1) {
					$ts = strtotime($date);
					if (date('Y', $ts) > date('Y')) {
						$darray = [ ];
						$darray [] = date('m', $ts);
						$darray [] = date('d', $ts);
						$darray [] = date('Y', $ts) - 100;
						$date = implode('/', $darray);
					}
					if (Helper::validateDate($date)) {
						$formatted = new \DateTime(date('Y-m-d', strtotime($date)));
					}
				}
				break;
			case 'number' :
				$limits = [ 
						'upper' => pow(2, 31) - 1,
						'lower' => -1 * (pow(2, 31) - 1) 
				];
				if (preg_match('/([\d\.]+)/i', $value, $match)) {
					$number = isset($match [1]) ? $match [1] : null;
					if (!$number || !is_numeric($number)) {
						$formatted = null;
					} else {
						$float = floatval($number);
						$formatted = ($float > $limits ['upper'] || $float < $limits ['lower']) ? null : $float;
					}
				}
				break;
			default :
				$formatted = $value;
				break;
		}
		return $formatted;
	}
}
