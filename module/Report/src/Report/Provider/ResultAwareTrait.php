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
use Agent\Entity\Filter;

/**
 *
 * @author arstropica
 *        
 */
trait ResultAwareTrait {

	/**
	 *
	 * @param Report $report        	
	 * @param number $limit        	
	 * @param string $sort        	
	 * @param string $order        	
	 * @param boolean $lazy        	
	 * @param boolean $active        	
	 * @param boolean $silent        	
	 *
	 * @return ArrayCollection $data
	 */
	public function generateResults(Report $report, $limit = 0, $sort = '_score', $order = 'desc', $lazy = true, $active = true, $silent = false)
	{
		$data = new ArrayCollection();
		$request = $this->getServiceLocator()
			->get('Request');
		$silent = $request->getQuery('debug') ? false : $silent;
		if ($report instanceof Report) {
			$agent = $report->getAgent();
			$lead_query = new \Agent\Elastica\Query\BoolQuery();
			if ($active) {
				$lead_query->addMust(new Elastica\Query\Match('active', 1));
			}
			$client = $this->getElasticaClient();
			if ($agent && $client) {
				$filters = $agent->getFilter();
				if ($filters) {
					$lead_query = $this->applyFilters($lead_query, $filters);
				}
				$criteria = $agent->getCriteria(false);
				if ($criteria) {
					try {
						$query = new Elastica\Query();
						/* @var $criterion \Agent\Entity\AgentCriterion */
						foreach ( $criteria as $i => $criterion ) {
							$lead_query = $this->buildQuery($lead_query, $criterion);
						}
						$query->setQuery($lead_query);
						$size = $limit ? $limit : 1000;
						$query->setParam('track_scores', true);
						$query->addSort([ 
								$sort => [ 
										'order' => $order 
								] 
						]);
						$query->setSize($size);
						$results = $this->runQuery($query, $lazy, $silent);
						$total = isset($results ['total']) ? $results ['total'] : false;
						if ($total && $results ['results']) {
							foreach ( $results ['results'] as $result ) {
								$data->add($result);
							}
						}
						
						if ($total > $size) {
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
	 * @param Filter $filter        	
	 *
	 * @return BoolQuery $lead_query
	 */
	protected function applyFilters(BoolQuery $lead_query, Filter $filter = null)
	{
		if ($filter) {
			$account = $filter->getAccountFilter();
			$date = $filter->getDateFilter();
			
			if ($account) {
				$mode = $account->getMode();
				switch ($mode) {
					case 'orphan' :
						$account_query = new Elastica\Query\Nested();
						$account_query->setPath('account')
							->setScoreMode('avg');
						$account_query->setQuery(new Elastica\Query\MatchAll());
						$lead_query->addMustNot($account_query);
						break;
					case 'account' :
						if (($account = $account->getAccount()) == true) {
							$account_id = $account->getId();
							$account_query = new Elastica\Query\Nested();
							$account_query->setPath('account')
								->setScoreMode('avg');
							$account_match_query = new Elastica\Query\Match('account.id', $account_id);
							$account_bool_query = new Elastica\Query\BoolQuery();
							$account_bool_query->addMust($account_match_query);
							$account_query->setQuery($account_bool_query);
							$lead_query->addMust($account_query);
						}
						break;
				}
			}
			
			if ($date) {
				$dates = [ ];
				$mode = $date->getMode();
				switch ($mode) {
					case null :
					case false :
						break;
					case 'timecreated' :
						if (($range = $date->getTimecreated()) == true) {
							$dates = explode(" - ", $range);
						}
						break;
					default :
						switch ($mode) {
							// Today
							case "1" :
								$dates = [ 
										date('Y-m-d\T00:00:00', time()),
										date('Y-m-d\T23:59:59', time()) 
								];
								break;
							// Last 7 days
							case "7" :
								$dates = [ 
										date('Y-m-d\T00:00:00', strtotime('-7 days')),
										date('Y-m-d\T23:59:59', time()) 
								];
								break;
							// Last 30 days
							case "30" :
								$dates = [ 
										date('Y-m-d\T00:00:00', strtotime('-30 days')),
										date('Y-m-d\T23:59:59', time()) 
								];
								break;
							// This Month
							case "month" :
								$dates = [ 
										date('Y-m-01\T00:00:00', time()),
										date('Y-m-t\T23:59:59', time()) 
								];
								break;
							// Last Month
							case "lmonth" :
								$dates = [ 
										date('Y-m-01\T00:00:00', strtotime('last month')),
										date('Y-m-t\T23:59:59', strtotime('last month')) 
								];
								break;
							// This Year
							case "year" :
								$dates = [ 
										date('Y-01-01\T00:00:00', time()),
										date('Y-m-d\T23:59:59', time()) 
								];
								break;
						
						}
						break;
				}
				if ($dates) {
					$date_query = new Elastica\Query\Range();
					foreach ( $dates as &$date ) {
						$time = strtotime($date);
						if ($time) {
							$date = date('Y-m-d\TH:i:s', $time);
						} else {
							$date = date('Y-m-d\TH:i:s', -9999999999);
						}
					}
					@list ( $from, $to ) = $dates;
					if (isset($from, $to)) {
						$date_query->addField('timecreated', [ 
								'gte' => $from,
								'lte' => $to 
						]);
						$lead_query->addMust($date_query);
					}
				}
			}
		}
		
		return $lead_query;
	}

	/**
	 *
	 * @param BoolQuery $lead_query        	
	 * @param AgentCriterion $criterion        	
	 *
	 * @return BoolQuery $lead_query
	 */
	protected function buildQuery(BoolQuery $lead_query, AgentCriterion $criterion)
	{
		if (!$criterion->getServiceLocator()) {
			$criterion->setServiceLocator($this->getServiceLocator());
		}
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
					}
				}
			}
		}
		return $lead_query;
	}

	/**
	 *
	 * @param AbstractQuery $query        	
	 * @param boolean $lazy        	
	 * @param boolean $silent        	
	 *
	 * @return array $results
	 */
	protected function runQuery($query, $lazy = false, $silent = false)
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
						$_score = isset($hit ['_score']) ? $hit ['_score'] : 1;
						$score = round(($_score / $max_score) * 100);
						$result = new Result($this->getServiceLocator());
						$result->setScore($score);
						if ($lazy) {
							$lead = new Lead();
							$lead->setId($hit ['_id']);
							$lead->setProxy(true);
						} else {
							$lead = $objRepository->findOneBy([ 
									'id' => $hit ['_id'] 
							]);
						}
						if ($lead && $lead instanceof Lead) {
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