<?php

namespace Report\Controller;

use Application\Controller\AbstractCrudController;
use Application\Provider\ElasticaAwareTrait;
use Application\Provider\SearchManagerAwareTrait;
use Application\Utility\Helper;
use Application\Provider\EventSourceAwareTrait;
use Zend\Http\Response;
use Application\Utility\EventSource\Job;
use Application\Provider\CacheAwareTrait;
use Application\Service\CacheAwareInterface;
use Zend\View\Model\JsonModel;
use Elastica;
use Elastica\Request;

/**
 *
 * @author arstropica
 *        
 */
class IndexController extends AbstractCrudController implements CacheAwareInterface {
	use ElasticaAwareTrait, SearchManagerAwareTrait, EventSourceAwareTrait, CacheAwareTrait;
	
	/**
	 *
	 * @var int
	 */
	protected $progress = 0;
	
	/**
	 *
	 * @var array
	 */
	protected $queue = [ ];
	
	/**
	 *
	 * @var int
	 */
	protected $eventId = null;
	
	/**
	 *
	 * @var string
	 */
	protected $token = null;

	/**
	 * (non-PHPdoc)
	 *
	 * @see \LosBase\Controller\ORM\AbstractCrudController::listAction()
	 */
	public function listAction()
	{
		$elastica_service = $this->getServiceLocator()
			->get('elastica-service');
		try {
			$elastica_service->setup();
			
			$entityClass = 'Lead\Entity\LeadAttribute';
			$results = [ 
					'elastica' => [ 
							'indices' => [ 
									'reports' => [ ] 
							] 
					],
					'doctrine' => [ 
							'attributes' => [ 
									'entities' => [ ],
									'types' => [ ] 
							] 
					] 
			];
			
			foreach ( $results ['elastica'] ['indices'] as $index => &$types ) {
				$elastica_index = $this->getElasticaIndex($index);
				
				if ($elastica_index->exists()) {
					$elastica_mapping = $elastica_index->getMapping();
					$types = array_keys($elastica_mapping);
				}
			}
			
			/* @var $chart Report\UI\Charts\HighCharts\HighChart */
			$chart = $this->highChart('solidgauge');
			$results ['chart'] = $chart->getChart();
			
			/* @var $qb \Doctrine\ORM\QueryBuilder */
			$qb = $this->getEntityManager()
				->createQueryBuilder();
			$qb->add('select', 'e')
				->add('from', $entityClass . ' e');
			
			$results ['doctrine'] ['attributes'] ['entities'] = $qb->getQuery()
				->getResult();
			
			/* @var $qb \Doctrine\ORM\QueryBuilder */
			$qb = $this->getEntityManager()
				->createQueryBuilder();
			$qb->add('select', 'e.attributeType')
				->add('from', $entityClass . ' e')
				->distinct(true);
			
			$results ['doctrine'] ['attributes'] ['types'] = $qb->getQuery()
				->getArrayResult();
			
			return $results;
		} catch ( \Exception $e ) {
			$this->flashMessenger()
				->addErrorMessage($e->getMessage());
			return $this->redirect()
				->toRoute('dashboard', [ ]);
		}
	}

	/**
	 * Build Action
	 *
	 * @return Response
	 */
	public function buildAction()
	{
		set_time_limit(0);
		ini_set('max_input_time', '3600');
		session_write_close();
		echo json_encode([ 
				'data' => 'Init Build.' 
		]);
		
		ob_end_clean();
		ignore_user_abort(true);
		header("Connection: Keep-Alive");
		$size = ob_get_length();
		header("Content-Length: $size");
		@ob_end_flush(); // Strange behavior; this will not work unless..
		flush(); // both functions are called !
		
		$response = $this->getResponse();
		$response->getHeaders()
			->addHeaders([ 
				'Connection' => 'close' 
		]);
		$response->setStatusCode(200);
		$result = false;
		
		$index = $this->params()
			->fromQuery('index');
		$type = $this->params()
			->fromQuery('type');
		$token = $this->params()
			->fromQuery('token');
		
		if ($token) {
			$this->token = $token;
		}
		$this->onBuild($index, $type);
		$result = $this->runQueue();
		
		$response->setContent(json_encode($result));
		return $response;
	}

	public function notifyAction()
	{
		$this->outputStreamHeaders();
		$response = $this->getResponse();
		$token = $this->params()
			->fromQuery('token');
		$id = $this->getEventID(false);
		if ($token) {
			$this->token = $token;
			$buffer = $this->getBuffer($id + 1);
			if ($buffer) {
				$response->setStatusCode(200);
				foreach ( $buffer as $i => $message ) {
					$id = $event = $data = null;
					extract($message);
					if (isset($id, $event, $data)) {
						$this->writeStream($id, $event, $data);
					}
				}
			} else {
				$waiting = [ 
						'event' => 'wait',
						'message' => [ 
								'status' => 'waiting',
								'message' => 'Waiting...' 
						] 
				];
				$this->writeStream($id, 'wait', $waiting);
				$response->setStatusCode(204);
			}
		}
		return $response;
	}

	public function dataAction()
	{
		$index = $this->getEvent()
			->getRouteMatch()
			->getParam('index', 'reports');
		
		$type = $this->getEvent()
			->getRouteMatch()
			->getParam('type', 'all');
		
		$results = [ 
				'data' => [ 
						'yAxis' => [ 
								'min' => 0,
								'max' => 100,
								'title' => [ 
										'y' => -120,
										'style' => array (
												'font-size' => '15px' 
										),
										'text' => ucwords($type) . "s" 
								],
								'tickPixelInterval' => 20 
						],
						'series' => [ 
								[ 
										'name' => ucwords($type),
										'data' => [ ],
										'dataLabels' => array (
												'format' => '<div style="text-align:center"><span style="font-size:25px;color:black">{y}</span><br/><span style="font-size:12px;color:silver">' . $type . 's</span></div>' 
										),
										'tooltip' => [ 
												'valueSuffix' => " {$type}(s)" 
										] 
								] 
						] 
				],
				'outcome' => 0 
		];
		
		$data = $this->_data($index, $type);
		$count = [ ];
		$count ['doctrine'] = (int) $data ['counts'] ['doctrine'] [$index] [$type];
		$count ['elastica'] = (int) $data ['counts'] ['elastica'] [$index] [$type];
		if ($data) {
			$results ['data'] ['yAxis'] ['max'] = $count ['doctrine'];
			$results ['data'] ['yAxis'] ['tickPixelInterval'] = max([ 
					($count ['doctrine'] / 5),
					50 
			]);
			$results ['data'] ['series'] [0] ['data'] [] = $count ['elastica'];
			$results ['outcome'] = 1;
		}
		return new JsonModel($results);
	}

	public function attributeAction()
	{
		$result = [ 
				'outcome' => 0 
		];
		$entityClass = 'Lead\Entity\LeadAttribute';
		$id = $this->getEvent()
			->getRouteMatch()
			->getParam('id', '0');
		
		$action = $this->params()
			->fromQuery('action', 'update');
		
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($entityClass);
		
		if ($id) {
			switch ($action) {
				case 'update' :
					$attributeType = $this->params()
						->fromQuery('type', false);
					
					if ($attributeType) {
						/* @var $entity \Lead\Entity\LeadAttribute */
						$entity = $objRepository->find($id);
						
						if ($entity && $entity instanceof $entityClass) {
							$entity->setAttributeType($attributeType);
							try {
								$em->persist($entity);
								$em->flush();
								$this->createServiceEvent()
									->setEntityId($id)
									->setEntityClass($entityClass)
									->setDescription("Lead Attribute Edited")
									->setMessage("Lead Attribute: " . $entity->getAttributeDesc() . " was edited.");
								$this->logEvent("EditAction.post");
								$result ['outcome'] = 1;
							} catch ( \Exception $e ) {
								$this->createServiceEvent()
									->setEntityId($id)
									->setEntityClass($entityClass)
									->setDescription("Lead Attribute Edited");
								$this->logError($e);
							}
						}
					
					}
					break;
				case 'relationship' :
					/* @var $entity \Lead\Entity\LeadAttribute */
					$entity = $objRepository->find($id);
					
					if ($entity && $entity instanceof $entityClass) {
						$attributeType = $entity->getAttributeType();
						
						/* @var $qb \Doctrine\ORM\QueryBuilder */
						$qb = $em->createQueryBuilder();
						$qb->add('select', 'e')
							->add('from', 'Agent\Entity\Relationship' . ' e')
							->where(' e.allowed LIKE :attributeType')
							->setParameter('attributeType', "%" . $attributeType . "%");
						
						$dql = $qb->getDQL();
						$query = $qb->getQuery();
						
						$query->useQueryCache(true);
						$query->useResultCache(true, 3600, 'relationship-' . md5($dql));
						
						$result ['data'] = $query->getArrayResult();
						$result ['outcome'] = 1;
					}
					break;
				case 'values' :
					$qb = $em->createQueryBuilder()
						->select('v.value')
						->from('Lead\Entity\LeadAttributeValue', 'v')
						->where('v.attribute = :id')
						->setParameter('id', $id)
						->distinct(true);
					
					$dql = $qb->getDQL();
					$query = $qb->getQuery();
					
					$query->useQueryCache(true);
					$query->useResultCache(true, 3600, 'leadattributevalue-' . md5($dql));
					
					$results = $query->getArrayResult();
					
					if ($results) {
						$result ['data'] = $results;
						$result ['outcome'] = 1;
					}
					break;
				case 'limits' :
					$attribute_query = new Elastica\Query\Nested();
					$attribute_query->setPath('attribute');
					$attribute_query->setQuery(new Elastica\Query\Match('attribute.id', $id));
					
					$value_filter = new Elastica\Filter\Query();
					$value_filter->setQuery($attribute_query);
					
					$max = new Elastica\Aggregation\Max('max_number');
					$max->setField('_number');
					$min = new Elastica\Aggregation\Min('min_number');
					$min->setField('_number');
					
					$aggs = new Elastica\Aggregation\Filter('numeric_range', $value_filter);
					$aggs->addAggregation($max)
						->addAggregation($min);
					
					$query = new Elastica\Query();
					$query->setSize(0);
					$query->addAggregation($aggs);
					
					/* @var $elastica_client Elastica\Client */
					$elastica_client = $this->getServiceLocator()
						->get('elastica-client');
					
					$result ['query'] = $query->toArray();
					try {
						/* @var $response \Elastica\Response */
						$response = $elastica_client->request('reports/value/_search', Request::GET, $query->toArray());
						$data = $response->getData();
						$limits = [ 
								'min' => 0,
								'max' => 0 
						];
						if (isset($data ['aggregations'] ['numeric_range']) && $data ['aggregations'] ['numeric_range'] ['doc_count'] > 0) {
							$limits ['min'] = $data ['aggregations'] ['numeric_range'] ['min_number'] ['value'];
							$limits ['max'] = $data ['aggregations'] ['numeric_range'] ['max_number'] ['value'];
						}
						$result ['data'] = $limits;
						$result ['outcome'] = 1;
					} catch ( \Exception $e ) {
						$result ['error'] = $e->getMessage();
					}
					break;
			}
		}
		
		return new JsonModel($result);
	}

	public function statsAction()
	{
		$index = $this->getEvent()
			->getRouteMatch()
			->getParam('index', 'reports');
		
		$results = [ 
				'stats' => [ ] 
		];
		
		$data = $this->_data($index, false, true);
		if ($data) {
			$results ['stats'] = $data ['stats'];
			$results ['outcome'] = 1;
		}
		return new JsonModel($results);
	}

	public function testAction()
	{}

	public function updateAction()
	{
		$batchSize = 20;
		$objRepository = $this->getEntityManager()
			->getRepository("Lead\\Entity\\Lead");
		$leads = $objRepository->findAll();
		for($i = 0; $i < count($leads); $i++) {
			$em = $this->getEntityManager();
			$lead = $leads [$i];
			try {
				if (($i % $batchSize) === 0) {
					$em->flush();
					$em->clear(); // Detaches all objects from Doctrine!
				}
			} catch ( \Exception $e ) {
			}
		}
		$em->flush(); // Persist objects that did not make up an entire batch
		$em->clear(); // Detaches all objects from Doctrine!
		return new JsonModel([ 
				count($leads) 
		]);
	}

	protected function _data($index, $type, $stats = false)
	{
		$results = [ 
				'counts' => [ 
						'elastica' => [ 
								$index => [ 
										$type => 0 
								] 
						] 
				],
				'stats' => [ 
						'elastica' => [ 
								$index => [ ] 
						] 
				] 
		];
		
		$elastica_index = $this->getElasticaIndex($index);
		
		if ($elastica_index->exists()) {
			if ($stats) {
				$results ['stats'] ['elastica'] [$index] = $this->getElasticaIndexStats($index);
			}
			if ($type) {
				$elastica_mapping = $elastica_index->getMapping();
				$elastica_types = array_keys($elastica_mapping);
				foreach ( $elastica_types as $elastica_type ) {
					if ($type == 'all' || $type == $elastica_type) {
						$results ['counts'] ['elastica'] [$index] [$elastica_type] = $this->getElasticaCount($index, $elastica_type);
						$results ['counts'] ['doctrine'] [$index] [$elastica_type] = $this->getEntityCount($this->getTypeNS($index, $elastica_type));
					}
				}
			}
		}
		return $results;
	}

	/**
	 * Build Index Jobs Queue
	 *
	 * @param string $index        	
	 * @param string $type        	
	 * @param number $batch        	
	 *
	 * @return void
	 */
	protected function onBuild($index, $type, $batch = 50)
	{
		$entityNS = $this->getTypeNS($index, $type);
		$total = $this->getEntityCount($entityNS);
		$this->addQueue(null, 0, [ 
				'layer' => 'doctrine',
				'index' => $index,
				'type' => $type,
				'count' => $total,
				'status' => 'running',
				"message" => "{$total} " . ucwords($type) . "s found." 
		]);
		
		$this->addQueue(null, 0, [ 
				'layer' => 'elastica',
				'index' => $index,
				'type' => $type,
				'count' => null,
				'status' => 'running',
				"message" => "Starting Index build." 
		]);
		
		if ($total && $total > 0) {
			$loop = ceil($total / $batch);
			$unit = (100 - $this->progress) / $loop;
			$count = 0;
			for($i = 0; $i < $loop; $i++) {
				$offset = $i * $batch;
				$onIndex = function () use($index, $type, $offset, $batch) {
					return $this->onIndex($index, $type, $offset, $batch);
				};
				$onMessage = function ($count) use($type, $index) {
					return [ 
							'layer' => 'elastica',
							'index' => $index,
							'type' => $type,
							'count' => $count,
							'status' => 'running',
							'message' => "{$count} " . ucwords($type) . "s processed." 
					];
				};
				$this->addQueue($onIndex, $unit, $onMessage);
			}
			$this->addQueue(null, 0, [ 
					"status" => "complete",
					"message" => "Operation Complete." 
			]);
		} else {
			$onFail = function () {
				return false;
			};
			$this->addQueue($onFail, 0, [ 
					"status" => "error",
					"message" => "Operation Failed." 
			]);
		}
	}

	/**
	 * Index creation handler
	 *
	 * @param string $index        	
	 * @param string $type        	
	 * @param string $offset        	
	 * @param number $limit        	
	 *
	 * @return bool
	 */
	protected function onIndex($index, $type, $offset, $limit = 10)
	{
		$entityNS = $this->getTypeNS($index, $type);
		/* @var $sm \Doctrine\Search\SearchManager */
		$sm = $this->getSearchManager();
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = $this->getEntityManager()
			->createQueryBuilder();
		$qb->add('select', 'e')
			->add('from', $entityNS . ' e')
			->setFirstResult($offset)
			->setMaxResults($limit);
		
		$result = $qb->getQuery()
			->getResult();
		try {
			$sm->persist($result);
			$sm->flush();
			$count = count($result) ? $offset + count($result) : false;
			return $count;
		} catch ( \Exception $e ) {
			$error = array_map(function ($r) {
				return $r->toArray();
			}, $result);
			$error ['error'] = $e->getMessage();
			$error ['trace'] = $e->getTrace();
			echo json_encode($error);
			return false;
		}
		return false;
	}

	/**
	 * Message Handler
	 *
	 * @param string $message        	
	 * @param string $event        	
	 * @param bool $echo        	
	 *
	 * @return array
	 */
	protected function onProgress($message, $event = 'notice', $output = true)
	{
		$data = [ 
				'event' => $event,
				'message' => $message,
				'progress' => number_format($this->progress, 2) 
		];
		if ($output) {
			$id = $this->getEventID();
			$buffer = compact([ 
					'id',
					'event',
					'data' 
			]);
			if (!$this->addBuffer($id, $buffer)) {
				$this->writeStream($id, $event, $data);
			}
			$this->eventId++;
		}
		return $data;
	}

	/**
	 * Get Event ID
	 *
	 * @param bool $inc        	
	 *
	 * @return int
	 */
	protected function getEventID($inc = true)
	{
		if (!$this->eventId) {
			$eventId = filter_input(INPUT_SERVER, 'HTTP_LAST_EVENT_ID');
			
			if (isset($eventId) && !empty($eventId) && is_numeric($eventId)) {
				$this->eventId = intval($eventId);
				if ($inc) {
					$this->eventId++;
				}
			} else {
				$this->eventId = 0;
			}
		}
		return $this->eventId;
	}

	/**
	 * Add job to Queue
	 *
	 * @param Closure|null $function        	
	 * @param int $progress        	
	 * @param string|Closure|null|array $message        	
	 *
	 * @return int
	 */
	protected function addQueue($function, $progress, $message = null)
	{
		$unitOfWork = new Job($progress, $message, $function);
		$index = count($this->queue);
		$this->queue [$index] = $unitOfWork;
		return $index;
	}

	/**
	 * Return message buffer
	 *
	 * @param number $after        	
	 * @return array
	 */
	protected function getBuffer($after = 0)
	{
		$buffer = [ ];
		if (isset($this->token)) {
			$cache = $this->getCache();
			if ($cache->hasItem($this->token)) {
				$raw = $cache->getItem($this->token);
				if ($raw) {
					$buffer = array_slice($raw, $after);
				}
			}
		}
		return $buffer;
	}

	/**
	 * Clear message Buffer
	 *
	 * @return void
	 */
	protected function clearBuffer()
	{
		if (isset($this->token)) {
			$cache = $this->getCache();
			if ($cache->hasItem($this->token)) {
				$cache->removeItem($this->token);
			}
		}
	}

	/**
	 * Add to message Buffer
	 *
	 * @param unknown $id        	
	 * @param unknown $message        	
	 *
	 * @return void
	 */
	protected function addBuffer($id, $message)
	{
		if (isset($this->token)) {
			if (!$id) {
				$this->clearBuffer();
			}
			
			$buffer = $this->getBuffer();
			$buffer [$id] = $message;
			$cache = $this->getCache();
			$cache->setItem($this->token, $buffer);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Run Jobs Queue
	 *
	 * @return mixed
	 */
	protected function runQueue()
	{
		$outcome = false;
		if ($this->queue) {
			$count = count($this->queue);
			$i = 0;
			foreach ( $this->queue as $job ) {
				$return = true;
				$message = false;
				$type = 'notice';
				if (isset($job->callback) && Helper::is_closure($job->callback)) {
					$return = $job->callback();
				}
				if (isset($job->progress)) {
					if (is_numeric($job->progress)) {
						$this->progress += $job->progress;
					} elseif (Helper::is_closure($job->progress)) {
						$this->progress += $job->progress($this->progress);
					}
				}
				if ($return && !$return instanceof \Exception) {
					if (Helper::is_closure($job->message)) {
						$message = $job->message($return);
					} elseif ($job->message) {
						$message = $job->message;
					} else {
						$message = [ 
								"status" => "running",
								"message" => "{$this->progress} completed." 
						];
					}
				} elseif ($return instanceof \Exception) {
					$message = [ 
							"status" => "running",
							"message" => $return->getMessage() 
					];
					$type = 'fail';
					break;
				} else {
					$message = [ 
							"status" => "error",
							"message" => "Oops. Something went wrong." 
					];
					$type = 'fail';
					break;
				}
				if (!$message) {
					$message = [ 
							"status" => "running",
							"message" => "{$this->progress} completed." 
					];
				}
				$i++;
				if ($count == $i) {
					$type = 'success';
				}
				$outcome = $this->onProgress($message, $type);
				sleep(1);
			}
		}
		return $outcome;
	}

	protected function _parse($unit, $oFields, &$hOptions)
	{
		$fullname = null;
		$defaults = array_combine($oFields, array_pad([ ], count($oFields), null));
		$args = array_merge($defaults, $unit);
		extract($args);
		$paths = explode(".", $fullname);
		krsort($paths);
		$current = &$hOptions;
		$lvls = count($paths);
		$i = 0;
		while ( count($paths) > 0 ) {
			$var = array_pop($paths);
			if (count($paths) === 0) {
				if (!isset($current [$var])) {
					$current [$var] = [ 
							'_meta' => $args 
					];
				}
			} else {
				if (!isset($current [$var])) {
					$current [$var] = [ ];
				}
				$current = &$current [$var];
			}
			$i++;
			if ($i >= $lvls || count($paths) === 0) {
				break;
			}
		}
	}

	protected function logEvent($event)
	{
		$this->getEventManager()
			->trigger($event, $this->getServiceEvent());
	}

	protected function logError(\Exception $e, $result = [])
	{
		$this->getServiceEvent()
			->setIsError(true);
		$this->getServiceEvent()
			->setMessage($e->getMessage());
		if ($result) {
			$this->getServiceEvent()
				->setResult(print_r($result, true));
		} else {
			$this->getServiceEvent()
				->setResult($e->getTraceAsString());
		}
		$this->logEvent('RuntimeError');
	}

}
