<?php
namespace Application\Provider;
use Doctrine\ORM\EntityManager;

/**
 *
 * @author arstropica
 *        
 */
trait EntityStorageAwareTrait
{

	/**
	 * Fetch all instances of Entity
	 *
	 * @param string $return        	
	 * @return multitype:
	 */
	function fetchAll ($return = 'array')
	{
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->add('select', 'e')->add('from', $this->getEntityClass() . ' e');
		$query = $qb->getQuery();
		return $return == 'array' ? $query->getArrayResult() : $query->getResult();
	}

	/**
	 * Fetch single instance of Entity
	 *
	 * @param integer $id        	
	 * @param string $return        	
	 * @return multitype:
	 */
	function fetch ($id, $return = 'array')
	{
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->add('select', 'e')->add('from', $this->getEntityClass() . ' e');
		$qb->where('e.id = :id');
		$qb->setParameter('id', $id);
		/* @var $query \Doctrine\ORM\Query */
		$query = $qb->getQuery();
		return $return == 'array' ? $query->getArrayResult() : $query->getResult();
	}

	/**
	 *
	 * Update Entity
	 *
	 * @param integer $id        	
	 * @param array $data        	
	 *
	 * @return integer
	 */
	function update ($id, $data = [])
	{
		$updated = 0;
		$params = [];
		$clauses = [];
		$dql = '';
		if ($data) {
			$params['id'] = $id;
			$dql = 'UPDATE ' . $this->getEntityClass() . ' e SET ';
			foreach ($data as $field => $value) {
				if ($field !== 'id') {
					$clauses[] = 'e.' . $field . ' = :' . $field;
					$params[$field] = $value;
				}
			}
			if ($clauses) {
				$dql .= implode(', ', $clauses);
				$dql .= ' WHERE e.id = :id';
				/* @var $query \Doctrine\ORM\Query */
				$query = $this->getEntityManager()->createQuery($dql);
				foreach ($params as $field => $value) {
					$query->setParameter($field, $value);
				}
				$updated = $query->execute();
			}
		}
		return $updated;
	}

	/**
	 * Insert Entity
	 *
	 * @param array $data        	
	 * @return number
	 */
	function insert ($data)
	{
		$success = 1;
		if ($data) {
			$entityClass = $this->getEntityClass();
			$entity = new $entityClass();
			$em = $this->getEntityManager();
			foreach ($data as $field => $value) {
				$method = 'set' . ucfirst($field);
				if (method_exists($entity, $method)) {
					try {
						$entity->{$method}($value);
					} catch (\Exception $e) {
						$success = 0;
					}
				}
			}
			if ($success) {
				try {
					$em->persist($entity);
					$success = 1;
				} catch (\Exception $e) {
					$success = 0;
				}
			}
		}
		return $success;
	}

	/**
	 * Batch Update
	 *
	 * @param array $collection        	
	 * @param number $size        	
	 *
	 * @return integer
	 */
	function bulkUpdate ($collection = [], $size = 20)
	{
		$updated = 0;
		if ($collection) {
			$em = $this->getEntityManager();
			foreach ($collection as $e) {
				if (isset($e['id'])) {
					$id = $e['id'];
					$data = $e;
					unset($data['id']);
					$updated += $this->update($id, $data);
				}
			}
		}
		return $updated;
	}

	/**
	 * Batch Insert
	 *
	 * @param array $collection        	
	 * @param number $size        	
	 *
	 * @return integer
	 */
	function bulkInsert ($collection = [], $size = 20)
	{
		$inserted = 0;
		if ($collection) {
			$em = $this->getEntityManager();
			for ($i = 1; $i <= count($collection); ++ $i) {
				$data = $collection[$i - 1];
				$insert += $this->insert($data);
				if (($i % $size) === 0) {
					$em->flush();
					$em->clear(); // Detaches all objects from Doctrine!
				}
			}
			$em->flush(); // Persist objects that did not make up an entire batch
			$em->clear();
		}
		return $inserted;
	}

	public abstract function getEntityClass ();

	public abstract function setEntityClass ($entityClass);

	public abstract function setEntityManager (EntityManager $em);

	public abstract function getEntityManager ();
}

?>