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

	public function findUnique ($reindex = false)
	{
		$dql = <<<DQL
		SELECT
			a
		FROM
			\Lead\Entity\LeadAttribute a
		GROUP BY 
			a.attributeDesc
		ORDER BY 
			a.attributeOrder
DQL;
		
		$query = $this->getEntityManager()->createQuery($dql);
		$query->useQueryCache(true);
		$query->useResultCache(true, 3600, md5($dql));
		$results = $query->getResult();
		if ($reindex) {
			$attributes = [];
			foreach ($results as $result) {
				$attributes[$result->getId()] = $result;
			}
			$results = $attributes;
		}
		return $results;
	}

	public function getUniqueArray ($invert = false)
	{
		$attributes = $this->findUnique(true);
		
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

	public function getAdminImportOptions ()
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

	public function getImportOptions ()
	{
		$prepend = [];
		$prepend_fields = [
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
