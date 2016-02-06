<?php

namespace Agent\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Elastica;
use Application\Provider\EntityDataTrait;
use Agent\Entity\Relationship\AbstractQuery;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Relationship
 *
 * @ORM\Table(name="agent_criterion_relationship")
 * @ORM\Entity(repositoryClass="Agent\Entity\Repository\RelationshipRepository")
 * @ORM\EntityListeners({ "Agent\Entity\Listener\RelationshipListener" })
 * @Annotation\Instance("Agent\Entity\Relationship")
 */
class Relationship {
	
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
	 * @var string @ORM\Column(name="description", type="string", length=255,
	 *      nullable=false)
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $description;
	
	/**
	 *
	 * @var string @ORM\Column(name="type", type="string", length=45,
	 *      nullable=false)
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $type;
	
	/**
	 *
	 * @var string @ORM\Column(name="symbol", type="string", length=45,
	 *      nullable=false)
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $symbol;
	
	/**
	 *
	 * @var array @ORM\Column(name="allowed", type="array")
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $allowed;
	
	/**
	 *
	 * @var string @ORM\Column(name="input", type="string", length=45,
	 *      nullable=false)
	 *      @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	private $input;
	
	/**
	 *
	 * @var AbstractQuery
	 */
	private $query;

	public function __construct()
	{
		$this->criteria = new ArrayCollection();
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
	 * @param integer $id        	
	 *
	 * @return Relationship
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 *
	 * @return string $description
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 *
	 * @param string $description        	
	 *
	 * @return Relationship
	 */
	public function setDescription($description)
	{
		$this->description = $description;
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
	 * @return Relationship
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 *
	 * @return string $symbol
	 */
	public function getSymbol()
	{
		return $this->symbol;
	}

	/**
	 *
	 * @param string $symbol        	
	 *
	 * @return Relationship
	 */
	public function setSymbol($symbol)
	{
		$this->symbol = $symbol;
		return $this;
	}

	/**
	 *
	 * @return array $allowed
	 */
	public function getAllowed()
	{
		return $this->allowed;
	}

	/**
	 *
	 * @param array $allowed        	
	 *
	 * @return Relationship
	 */
	public function setAllowed($allowed)
	{
		$this->allowed = $allowed;
		return $this;
	}

	/**
	 *
	 * @return string $input
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 *
	 * @param string $input        	
	 *
	 * @return Relationship
	 */
	public function setInput($input)
	{
		$this->input = $input;
		return $this;
	}

	/**
	 *
	 * @param mixed $value        	
	 *
	 * @return AbstractQuery $query
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 *
	 * @param AbstractQuery $query        	
	 *
	 * @return Relationship
	 */
	public function setQuery($query)
	{
		$this->query = $query;
		return $this;
	}
}

?>