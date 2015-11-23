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

    public function findUnique($reindex = false, $criteria = false)
    {
        $where = [];
        $params = [];
        if ($criteria) {
            foreach ($criteria as $field => $criterion) {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $criterion;
            }
        }
        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->add('select', 'a')
            ->add('from', '\Lead\Entity\LeadAttribute a')
            ->groupBy('a.attributeDesc')
            ->orderBy('a.attributeOrder');
        
        if ($where) {
            foreach ($where as $condition) {
                $qb->andWhere($condition);
            }
        }
        if ($params) {
            foreach ($params as $key => $value) {
                $qb->setParameter($key, $value);
            }
        }
        
        $dql = $qb->getDQL();
        $query = $qb->getQuery();
        
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

    public function getUniqueArray($invert = false, $criteria = [])
    {
        $attributes = $this->findUnique(true, $criteria);
        
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

    public function getAdminImportOptions()
    {
        $prepend = [];
        $prepend_fields = [
            "new" => [
                "name" => "Question",
                "desc" => "Add New Field"
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

    public function getImportOptions()
    {
        $prepend = [];
        $prepend_fields = [
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

    public function getCount()
    {
        $dql = <<<DQL
		SELECT 
			COUNT(a.id)
		FROM
			\Lead\Entity\LeadAttribute a
DQL;
        
        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getSingleScalarResult();
    }

    public function getIDFromDesc($desc)
    {
        $id = false;
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->add('select', 'a')
            ->add('from', '\Lead\Entity\LeadAttribute a')
            ->setMaxResults(1)
            ->andWhere('a.attributeDesc = :desc')
            ->setParameter('desc', $desc);
        
        $dql = $qb->getDQL();
        $query = $qb->getQuery();
        
        $query->useQueryCache(true);
        $query->useResultCache(true, 3600, md5($dql));
        $results = $query->getResult();
        
        if ($results && count($results) > 0) {
            $id = $results[0]->getId();
        }
        return $id;
    }

    public function getDescFromID($id)
    {
        $desc = false;
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->add('select', 'a')
            ->add('from', '\Lead\Entity\LeadAttribute a')
            ->setMaxResults(1)
            ->andWhere('a.id = :id')
            ->setParameter('id', $id);
        
        $dql = $qb->getDQL();
        $query = $qb->getQuery();
        
        $query->useQueryCache(true);
        $query->useResultCache(true, 3600, md5($dql));
        $results = $query->getResult();
        
        if ($results && count($results) > 0) {
            $desc = $results[0]->getAttributeDesc();
        }
        return $desc;
    }
}
