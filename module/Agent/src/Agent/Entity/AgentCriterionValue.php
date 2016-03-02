<?php

namespace Agent\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Provider\EntityDataTrait;
use Application\Utility\Helper;

/**
 * AgentCriterionValue
 *
 * @ORM\Table(name="agent_criterion_values")
 * @ORM\Entity
 * @ORM\EntityListeners({ "Agent\Entity\Listener\AgentCriterionValueListener" })
 * @Annotation\Instance("\Agent\Entity\AgentCriterionValue")
 */
class AgentCriterionValue {
	
	use EntityDataTrait;
	
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
	 * @var Collection @ORM\OneToMany(targetEntity="Agent\Entity\AgentCriterionValue",
	 *      mappedBy="parent")
	 */
	protected $children;
	
	/**
	 *
	 * @var \Agent\Entity\AgentCriterionValue @ORM\ManyToOne(targetEntity="Agent\Entity\AgentCriterionValue",
	 *      inversedBy="children")
	 *      @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent;
	
	/**
	 *
	 * @var \Agent\Entity\AgentCriterion @ORM\OneToOne(targetEntity="Agent\Entity\AgentCriterion",
	 *      inversedBy="value", cascade={"persist"})
	 *      @ORM\JoinColumn(name="criterion_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $criterion;
	
	/**
	 *
	 * @var string @ORM\Column(name="type", type="string", length=45,
	 *      nullable=false)
	 */
	protected $type;
	
	/**
	 *
	 * @var string @ORM\Column(name="value", type="text", length=65535,
	 *      nullable=true)
	 *      @Annotation\Exclude()
	 */
	protected $value;
	
	/**
	 *
	 * @var string @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $string;
	
	/**
	 *
	 * @var string @ORM\Column(type="string", length=3, nullable=true)
	 */
	protected $boolean;
	
	/**
	 *
	 * @var string @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $daterange;
	
	/**
	 *
	 * @var array @ORM\Column(type="array", nullable=true)
	 */
	protected $location;
	
	/**
	 *
	 * @var array @ORM\Column(type="array", nullable=true)
	 */
	protected $multiple;
	
	/**
	 *
	 * @var string @ORM\Column(name="`range`", type="text", length=65535,
	 *      nullable=true)
	 */
	protected $range;

	function __construct()
	{
		$this->collection = new ArrayCollection();
		$this->children = new ArrayCollection();
	}

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
	 * @return AgentCriterion $criterion
	 */
	public function getCriterion()
	{
		return $this->criterion;
	}

	/**
	 *
	 * @param \Agent\Entity\AgentCriterion $criterion        	
	 *
	 * @return AgentCriterionValue
	 */
	public function setCriterion($criterion)
	{
		$this->criterion = $criterion;
		return $this;
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
	 * @param string $type        	
	 *
	 * @return AgentCriterionValue
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 *
	 * @param boolean $ac        	
	 *
	 * @return Collection $children
	 */
	public function getChildren($ac = false)
	{
		return $this->children;
	}

	/**
	 *
	 * @param \Doctrine\Common\Collections\Collection $children        	
	 *
	 * @return AgentCriterionValue
	 */
	public function setChildren($children)
	{
		$this->children = $children;
		return $this;
	}

	/**
	 * Add children to AgentCriterionValue.
	 *
	 * @param Collection $children        	
	 *
	 * @return void
	 */
	public function addChildren(Collection $children)
	{
		foreach ( $children as $value ) {
			if (!$this->children->contains($value)) {
				$this->children->add($value);
				$value->setCriterion($this->getCriterion());
			}
		}
	}

	/**
	 *
	 * @param Collection $children        	
	 *
	 * @return AgentCriterionValue
	 */
	public function removeChildren(Collection $children)
	{
		foreach ( $children as $value ) {
			if ($this->children->contains($value)) {
				$this->children->removeElement($value);
				$value->setCriterion(null);
			}
		}
		
		return $this;
	}

	/**
	 *
	 * @return AgentCriterionValue $parent
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 *
	 * @param \Agent\Entity\AgentCriterionValue $parent        	
	 *
	 * @return AgentCriterionValue
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 *
	 * @return mixed $value
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 *
	 * @param mixed $value        	
	 *
	 * @return AgentCriterionValue
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	/**
	 *
	 * @return string $string
	 */
	public function getString()
	{
		return $this->string;
	}

	/**
	 *
	 * @param string $string        	
	 *
	 * @return AgentCriterionValue
	 */
	public function setString($string)
	{
		$this->string = $string;
		return $this;
	}

	/**
	 *
	 * @return string $boolean
	 */
	public function getBoolean()
	{
		return $this->boolean;
	}

	/**
	 *
	 * @param string $boolean        	
	 *
	 * @return AgentCriterionValue
	 */
	public function setBoolean($boolean)
	{
		$this->boolean = $boolean;
		return $this;
	}

	/**
	 *
	 * @return string $daterange
	 */
	public function getDaterange()
	{
		return $this->daterange;
	}

	/**
	 *
	 * @param string $daterange        	
	 *
	 * @return AgentCriterionValue
	 */
	public function setDaterange($daterange)
	{
		$this->daterange = $daterange;
		return $this;
	}

	/**
	 *
	 * @return array $location
	 */
	public function getLocation()
	{
		return $this->location;
	}

	/**
	 *
	 * @param array $location        	
	 *
	 * @return AgentCriterionValue
	 */
	public function setLocation($location)
	{
		$this->location = $location;
		return $this;
	}

	/**
	 *
	 * @return array $multiple
	 */
	public function getMultiple()
	{
		return $this->multiple;
	}

	/**
	 *
	 * @param array $multiple        	
	 *
	 * @return AgentCriterionValue
	 */
	public function setMultiple($multiple)
	{
		$this->multiple = $multiple;
		return $this;
	}

	/**
	 *
	 * @return string $range
	 */
	public function getRange()
	{
		return $this->range;
	}

	/**
	 *
	 * @param string $range        	
	 *
	 * @return AgentCriterionValue
	 */
	public function setRange($range)
	{
		$this->range = $range;
		return $this;
	}

	public function getData()
	{
		$data = false;
		$criterion = $this->getCriterion();
		if ($criterion) {
			$relationship = $criterion->getRelationship();
			if ($relationship) {
				$type = $relationship->getInput();
				if ($type) {
					$methodName = "get" . ucwords($type);
					if (method_exists($this, $methodName)) {
						switch ($type) {
							case 'boolean' :
							case 'string' :
							case 'location' :
							case 'multiple' :
								$data = $this->{$methodName}();
								break;
							case 'daterange' :
								$dates = explode(" - ", $this->{$methodName}());
								if ($dates) {
									$data = array_map(function ($date) {
										return date('Y-m-d H:i:s', strtotime($date));
									}, $dates);
								}
								break;
							case 'range' :
								$data = explode(",", $this->{$methodName}());
								break;
							default :
								$data = $this->{$methodName}();
								break;
						}
					}
				}
			}
		}
		return $data;
	}

	public function __toString()
	{
		$fValue = $this->getValue();
		$criterion = $this->getCriterion();
		if ($criterion) {
			$relationship = $criterion->getRelationship();
			if ($relationship) {
				$type = $relationship->getInput();
				if ($type) {
					$methodName = "get" . ucwords($type);
					if (method_exists($this, $methodName)) {
						switch ($type) {
							case 'boolean' :
							case 'string' :
							case 'daterange' :
							case 'range' :
								$fValue = $this->{$methodName}();
								break;
							case 'location' :
							case 'multiple' :
								$value = $this->{$methodName}();
								$fValue = is_array($value) ? Helper::recursive_implode($value, ", ", false) : $value;
								break;
						}
					}
				}
			}
		}
		return $fValue;
	}
}

?>