<?php

namespace Agent\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Lead\Entity\LeadAttribute;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Application\Provider\ServiceLocatorAwareTrait;
use Application\Provider\EntityDataTrait;

/**
 * AgentCriterion
 *
 * @ORM\Table(name="agent_criteria")
 * @ORM\Entity
 * @Annotation\Instance("\Agent\Entity\AgentCriterion")
 */
class AgentCriterion implements ServiceLocatorAwareInterface {
	use ServiceLocatorAwareTrait, EntityDataTrait;
	
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
	 * @var \Lead\Entity\LeadAttribute @ORM\ManyToOne(targetEntity="Lead\Entity\LeadAttribute",
	 *      inversedBy="values", cascade={"persist", "remove"})
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
	 *      })
	 */
	private $attribute;
	
	/**
	 *
	 * @var \Agent\Entity\Agent @ORM\ManyToOne(targetEntity="Agent\Entity\Agent",
	 *      inversedBy="criteria", cascade={"persist"})
	 *      @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="SET NULL")
	 *      })
	 */
	private $agent;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Agent\Entity\Relationship")
	 * @ORM\JoinColumn(name="relationship_id",
	 * referencedColumnName="id",
	 * nullable=false)
	 */
	private $relationship;
	
	/**
	 *
	 * @var float @ORM\Column(name="weight", type="float", length=3,
	 *      nullable=true)
	 */
	private $weight;
	
	/**
	 *
	 * @var integer @ORM\Column(name="required", type="integer", length=1,
	 *      nullable=true)
	 */
	private $required;
	
	/**
	 *
	 * @var AgentCriterionValue @ORM\OneToOne(targetEntity="Agent\Entity\AgentCriterionValue",
	 *      mappedBy="criterion", cascade={"persist", "remove"},
	 *      fetch="EXTRA_LAZY")
	 *      @Annotation\Exclude()
	 */
	private $value;

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
	 * @return LeadAttribute $attribute
	 */
	public function getAttribute()
	{
		return $this->attribute;
	}

	/**
	 *
	 * @param \Lead\Entity\LeadAttribute $attribute        	
	 *
	 * @return AgentCriterion
	 */
	public function setAttribute($attribute)
	{
		$this->attribute = $attribute;
		return $this;
	}

	/**
	 *
	 * @return Agent $agent
	 */
	public function getAgent()
	{
		return $this->agent;
	}

	/**
	 *
	 * @param \Agent\Entity\Agent $agent        	
	 *
	 * @return AgentCriterion
	 */
	public function setAgent($agent)
	{
		$this->agent = $agent;
		return $this;
	}

	/**
	 *
	 * @return Relationship $relationship
	 */
	public function getRelationship()
	{
		return $this->relationship;
	}

	/**
	 *
	 * @param Relationship $relationship        	
	 *
	 * @return AgentCriterion
	 */
	public function setRelationship($relationship)
	{
		$this->relationship = $relationship;
		return $this;
	}

	/**
	 *
	 * @return float $weight
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 *
	 * @param float $weight        	
	 *
	 * @return AgentCriterion
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
		return $this;
	}

	/**
	 *
	 * @return integer $required
	 */
	public function getRequired()
	{
		return $this->required;
	}

	/**
	 *
	 * @param integer $required        	
	 *
	 * @return AgentCriterion
	 */
	public function setRequired($required)
	{
		$this->required = $required;
		return $this;
	}

	/**
	 *
	 * @return AgentCriterionValue $value
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 *
	 * @param AgentCriterionValue $value        	
	 *
	 * @return AgentCriterion
	 */
	public function setValue($value)
	{
		$value->setCriterion($this);
		$this->value = $value;
		return $this;
	}

	/**
	 *
	 * @return string Type Field
	 */
	public function getTypeField()
	{
		$type = false;
		$attribute = $this->getAttribute();
		if ($attribute && $attribute instanceof LeadAttribute) {
			$type = $attribute->getAttributeType();
		}
		switch ($type) {
			case 'date' :
				return '_date';
			case 'integer' :
			case 'number' :
				return '_number';
			default :
				return 'value';
				break;
		}
		return 'value';
	}
}

?>