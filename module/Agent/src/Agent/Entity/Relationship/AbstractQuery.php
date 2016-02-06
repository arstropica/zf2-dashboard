<?php

namespace Agent\Entity\Relationship;

use Agent\Entity\Relationship;
use Agent\Elastica\Query\BoolQuery;
use Elastica;
use Application\Provider\EntityDataTrait;
use Agent\Entity\AgentCriterion;

/**
 *
 * @author arstropica
 *        
 */
abstract class AbstractQuery {
	use EntityDataTrait;
	
	/**
	 *
	 * @var Relationship
	 */
	private $relationship;
	
	/**
	 *
	 * @var AgentCriterion
	 */
	private $criterion;
	
	/**
	 *
	 * @var BoolQuery
	 */
	private $query;

	/**
	 *
	 * @param AgentCriterion $criterion        	
	 */
	public function __construct($criterion = null)
	{
		$this->setCriterion($criterion);
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
	 * @param \Agent\Entity\Relationship $relationship        	
	 *
	 * @return AbstractQuery
	 */
	public function setRelationship($relationship)
	{
		$this->relationship = $relationship;
		return $this;
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
	 * @return AbstractQuery
	 */
	public function setCriterion($criterion)
	{
		if ($criterion) {
			$relationship = $criterion->getRelationship();
			if ($relationship) {
				$this->setRelationship($relationship);
			}
		} else {
			$this->setRelationship(null);
		}
		$this->criterion = $criterion;
		return $this;
	}

	/**
	 *
	 * @param mixed $value        	
	 * @param BoolQuery $query        	
	 * @param integer|boolean $required        	
	 * @param integer|float $boost        	
	 *
	 * @return BoolQuery $query
	 */
	public function getQuery($value = null, $query = null, $required = null, $boost = null)
	{
		$relationship = $this->getRelationship();
		if ($relationship) {
			$attributeDesc = false;
			$criterion = $this->getCriterion();
			if ($criterion) {
				$attribute = $criterion->getAttribute();
				if ($attribute) {
					$attributeDesc = $attribute->getAttributeDesc();
				}
			}
			
			if ($attributeDesc) {
				if (!isset($query)) {
					$query = new BoolQuery();
				}
				
				$attribute_query = new Elastica\Query\Nested();
				$attribute_query->setPath('attribute');
				
				$attribute_query->setQuery(new Elastica\Query\Match('attribute.attributeDesc', $attributeDesc));
				
				$query->addMust($attribute_query);
			}
			$this->setQuery($query);
		}
		return $this->query;
	}

	/**
	 *
	 * @param \Agent\Elastica\AbstractQuery\BoolQuery $query        	
	 *
	 * @return AbstractQuery
	 */
	public function setQuery($query)
	{
		$this->query = $query;
		return $this;
	}

}

?>