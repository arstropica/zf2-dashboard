<?php

namespace Report\Provider;

use Zend\ServiceManager\ServiceLocatorInterface;
use Elastica\Client;
use Report\Entity\Report;
use Elastica;
use Elastica\Request;
use Report\Entity\Result;
use Doctrine\Common\Collections\ArrayCollection;
use Lead\Entity\Lead;
use Account\Entity\Account;
use Agent\Entity\AgentCriterion;
use Agent\Elastica\Query\BoolQuery;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Elastica\Query\AbstractQuery;

/**
 *
 * @author arstropica
 *        
 */
trait ResultAwareTrait {
	
	/**
	 *
	 * @var boolean
	 */
	private $hasAccount;

	/**
	 *
	 * @param Report $report        	
	 * @param boolean $silent        	
	 *
	 * @return ArrayCollection $data
	 */
	public function generateResults(Report $report, $limit = 0, $sort = '_score', $order = 'desc', $silent = false)
	{
		$data = new ArrayCollection();
		$request = $this->getServiceLocator()
			->get('Request');
		$silent = $request->getQuery('debug') ? false : $silent;
		if ($report instanceof Report) {
			$agent = $report->getAgent();
			// $account = $report->getAccount();
			$this->hasAccount = false;
			$lead_query = new \Agent\Elastica\Query\BoolQuery();
			$client = $this->getElasticaClient();
			if ($agent && $client) {
				$account = $agent->getOrphan() ? false : $agent->getAccount();
				$criteria = $agent->getCriteria(false);
				if ($criteria) {
					try {
						$query = new Elastica\Query();
						/* @var $criterion \Agent\Entity\AgentCriterion */
						foreach ( $criteria as $i => $criterion ) {
							$lead_query = $this->buildQuery($lead_query, $criterion, $account);
						}
						$query->setQuery($lead_query);
						$size = 10;
						$query->setParam('track_scores', true);
						$query->addSort([ 
								$sort => [ 
										'order' => $order 
								] 
						]);
						$query->setSize($size);
						$results = $this->runQuery($query, $silent);
						$total = isset($results ['total']) ? $results ['total'] : false;
						if ($total && $results ['results']) {
							foreach ( $results ['results'] as $result ) {
								$data->add($result);
							}
						}
						
						if ($total > 10) {
							$limit = $limit ?  : $total;
							for($page = 1; $page < ceil($limit / $size); $page++) {
								$query->setFrom($page * $size);
								$results = $this->runQuery($query, $silent);
								if ($results ['results']) {
									foreach ( $results as $result ) {
										if (is_array($result)) {
											foreach ( $result as $r ) {
												if ($r instanceof Result) {
													$data->add($r);
												}
											}
										} elseif ($result instanceof Result) {
											$data->add($result);
										}
									}
								}
							}
						}
					} catch ( \Exception $e ) {
						if (!$silent) {
							$this->getFlashMessenger()
								->addErrorMessage($e->getMessage());
						}
					}
				}
			}
		}
		return $data;
	}

	/**
	 *
	 * @param BoolQuery $lead_query        	
	 * @param AgentCriterion $criterion        	
	 * @param Account|boolean $account        	
	 *
	 * @return BoolQuery $lead_query
	 */
	protected function buildQuery(BoolQuery $lead_query, AgentCriterion $criterion, $account = null)
	{
		$criteria_query = false;
		$relationship = $criterion->getRelationship();
		$required = $criterion->getRequired();
		$weight = $criterion->getWeight();
		if ($relationship) {
			$value = $criterion->getValue();
			if ($value) {
				$data = $value->getData();
				$boost = $weight ? ((1 - $weight) * 10) + 1 : 1;
				$abstractQuery = $relationship->getQuery();
				if ($abstractQuery) {
					$abstractQuery->setCriterion($criterion);
					$abstractQuery->setRelationship($relationship);
					$request = $this->getServiceLocator()
						->get('Request');
					$debug = $request->getQuery('debug');
					if ($debug) {
						echo "<script>console.dir(" . json_encode($data) . ");</script>\n";
					}
					$boolQuery = $abstractQuery->getQuery($data, null, $required, $boost);
					if ($boolQuery) {
						switch ($relationship->getType()) {
							case 'location' :
								$queryClass = (new \ReflectionClass($boolQuery))->getShortName();
								switch ($queryClass) {
									case 'GeoDistance' :
										$criteria_query = $boolQuery;
										break;
									default :
										$criteria_query = new Elastica\Query\HasChild($boolQuery, 'value');
										$criteria_query->setParam('score_mode', 'avg');
										break;
								}
								break;
							default :
								$criteria_query = new Elastica\Query\HasChild($boolQuery, 'value');
								$criteria_query->setParam('score_mode', 'avg');
								break;
						}
						if ($required) {
							$lead_query->addMust($criteria_query);
						} else {
							$lead_query->addShould($criteria_query);
							$lead_query->setBoost($boost);
						}
						if (($account || $account === false) && !$this->hasAccount) {
							$account_query = new Elastica\Query\Nested();
							$account_query->setPath('account')
								->setScoreMode('avg');
							if ($account) {
								$account_match_query = new Elastica\Query\Match('account.id', $account->getId());
								$account_bool_query = new Elastica\Query\BoolQuery();
								$account_bool_query->addMust($account_match_query);
								$account_query->setQuery($account_bool_query);
								$lead_query->addMust($account_query);
							} else {
								$account_query->setQuery(new Elastica\Query\MatchAll());
								$lead_query->addMustNot($account_query);
							}
							$this->hasAccount = true;
						}
					}
				}
			}
		}
		return $lead_query;
	}

	/**
	 *
	 * @param AbstractQuery $query        	
	 * @param boolean $silent        	
	 *
	 * @return array $results
	 */
	protected function runQuery($query, $silent = false)
	{
		$results = [ 
				'results' => [ ],
				'total' => 0 
		];
		try {
			$request = $this->getServiceLocator()
				->get('Request');
			$debug = $request->getQuery('debug');
			if ($debug) {
				echo "<script>console.log('" . addSlashes(json_encode($query->toArray())) . "');</script>\n";
			}
			$client = $this->getElasticaClient();
			$response = $client->request('reports/lead/_search?query_cache=true', Request::GET, $query->toArray());
			if ($response && $response->isOk()) {
				$objRepository = $this->getObjectManager()
					->getRepository("Lead\\Entity\\Lead");
				$data = $response->getData();
				if ($data && isset($data ['hits'] ['hits'])) {
					$hits = $data ['hits'] ['hits'];
					$max_score = $data ['hits'] ['max_score'] ?  : 1;
					$results ['total'] = $data ['hits'] ['total'];
					foreach ( $hits as $hit ) {
						$lead = $objRepository->findOneBy([ 
								'id' => $hit ['_id'] 
						]);
						if ($lead && $lead instanceof Lead) {
							$result = new Result();
							$score = round(($hit ['_score'] / $max_score) * 100);
							$result->setScore($score);
							$result->setLead($lead);
							$results ['results'] [] = $result;
						}
					}
				}
			}
		} catch ( \Exception $e ) {
			if (!$silent) {
				$this->getFlashMessenger()
					->addErrorMessage($e->getMessage());
			}
		}
		return $results;
	}

	/**
	 * Set service locator
	 *
	 * @param ServiceLocatorInterface $serviceLocator        	
	 * @return mixed
	 */
	abstract public function setServiceLocator(ServiceLocatorInterface $serviceLocator);

	/**
	 * Get service locator
	 *
	 * @return ServiceLocatorInterface
	 */
	abstract public function getServiceLocator();

	/**
	 * Get Elastica Client
	 *
	 * @return Client
	 */
	abstract public function getElasticaClient();

	/**
	 * Set Elasta Client
	 *
	 * @param Client $client        	
	 */
	abstract public function setElasticaClient(Client $client);

	/**
	 * Set the object manager
	 *
	 * @param ObjectManager $objectManager        	
	 */
	abstract public function setObjectManager(ObjectManager $objectManager);

	/**
	 * Get the object manager
	 *
	 * @return ObjectManager
	 */
	abstract public function getObjectManager();

	/**
	 *
	 * @return FlashMessenger
	 */
	abstract public function getFlashMessenger();

	/**
	 *
	 * @param FlashMessenger $flashMessenger        	
	 */
	abstract public function setFlashMessenger(FlashMessenger $flashMessenger);

}

?>