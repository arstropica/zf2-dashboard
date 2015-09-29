<?php
namespace Lead\Entity\Repository;

/**
 *
 * @author arstropica
 *        
 */
use Doctrine\ORM\EntityRepository;
use Lead\Entity\LeadAttribute;

class LeadAttributeRepository extends EntityRepository
{

	public function findUnique ()
	{
		$dql = <<<DQL
		SELECT
			a
		FROM
			\Lead\Entity\LeadAttribute a
		GROUP BY 
			a.attributeDesc
		ORDER BY 
			a.id
DQL;
		
		$query = $this->getEntityManager()->createQuery($dql);
		
		$results = $query->getResult();
		$attributes = [];
		foreach ($results as $result) {
			$attributes[$result->getId()] = $result;
		}
		return $attributes;
	}

	public function getUniqueArray ($invert = false)
	{
		$attributes = $this->findUnique();
		
		$results = [];
		
		foreach ($attributes as $attribute) {
			if ($invert) {
				$results[$attribute->getAttributeDesc()] = $attribute->getId();
			} else {
				$results[$attribute->getId()] = $attribute->getAttributeDesc();
			}
		}
		return $results;
	}

	public function getImportOptions ()
	{
		$prepend = [];
		$prepend_fields = [
				"new" => [
						"name" => "Question",
						"desc" => "Add New Field"
				],
				"ignore" => [
						"name" => "ignore",
						"desc" => "Ignore Field"
				],
				"timecreated" => [
						"name" => "timecreated",
						"desc" => "Time Created"
				],
				"referrer" => [
						"name" => "referrer",
						"desc" => "Referrer"
				],
				"ipaddress" => [
						"name" => "ipaddress",
						"desc" => "IP Address"
				]
		];
		$attributes = $this->findUnique();
		foreach ($prepend_fields as $key => $array) {
			$prepend[$key] = new LeadAttribute();
			$prepend[$key]->setAttributeName($array['name']);
			$prepend[$key]->setAttributeDesc($array['desc']);
			$prepend[$key]->setId($array['name']);
		}
		
		return array_merge($prepend, $attributes);
	}
}
