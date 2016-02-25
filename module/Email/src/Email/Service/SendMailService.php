<?php

namespace Email\Service;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\Mail;
use Zend\Mime;

/**
 *
 * @author arstropica
 *        
 */
class SendMailService extends AbstractEmailService implements EventManagerAwareInterface {

	/*
	 * (non-PHPdoc)
	 * @see \Email\Service\AbstractEmailService::send()
	 */
	public function send($id, $service = 'Email')
	{
		$this->getServiceEvent()
			->setEntityId($id)
			->setEntityClass('Lead\Entity\Lead')
			->setDescription('Email Sent (' . $service . ')')
			->setResult('');
		
		if (!$this->checkAuth()) {
			return $this->respondError(new \Exception('Insufficient User Authorization.', 401));
		}
		
		$options = $this->getOptions($id, $service);
		$smtp_options = false;
		
		if ($options) {
			$fields = [ 
					'address_from' => null,
					'address_to' => null,
					'subject' => null 
			];
			
			foreach ( $options as $scope => $settings ) {
				foreach ( $settings as $option => $value ) {
					if ($value) {
						switch ($option) {
							case 'address_to' :
								switch ($scope) {
									case 'global' :
										break;
									case 'local' :
										$fields [$option] = $value;
										break;
								}
								break;
							case 'smtp' :
								switch ($scope) {
									case 'global' :
										$smtp_options = $value;
										break;
								}
								break;
							default :
								switch ($scope) {
									case 'global' :
										if (!isset($fields [$option])) {
											$fields [$option] = is_array($value) ? end($value) : $value;
										}
										break;
									case 'local' :
										$fields [$option] = is_array($value) ? end($value) : $value;
										break;
								}
								break;
						}
					}
				}
			}
			
			$address_from = $address_to = $subject = $html = $text = null;
			extract($fields);
			
			if (isset($address_from, $address_to, $subject)) {
				$data = $this->getData($id);
				$parts = [ ];
				if ($data) {
					extract($data);
					
					$config = $this->getServiceLocator()
						->get('Config');
					
					$siteTitle = $config ['site'] ['title'];
					
					$body = new Mime\Message();
					
					if (isset($text)) {
						$parts ['text'] = new Mime\Part($text);
						$parts ['text']->type = "text/plain";
					}
					if (isset($html)) {
						$parts ['html'] = new Mime\Part($html);
						$parts ['html']->type = "text/html";
					}
					
					$message = new Mail\Message();
					switch ($service) {
						case 'Email' :
						default :
							$body->setParts($parts);
							$message->setBody($body);
							$message->getHeaders()
								->get('content-type')
								->setType('multipart/alternative');
							break;
						case 'WebWorks' :
							$body = isset($text) ? $text : '';
							$message->setBody($body);
							break;
					}
					
					if (is_array($address_to)) {
						$address_to = array_map('strtolower', $address_to);
					} elseif (is_string($address_to)) {
						$address_to = strtolower($address_to);
					}
					
					if (is_array($address_from)) {
						$address_from = array_map('strtolower', $address_from);
					} elseif (is_string($address_from)) {
						$address_from = strtolower($address_from);
					}
					
					$message->setFrom($address_from);
					$message->addTo($address_to);
					
					$message->setSender($address_from, $siteTitle);
					$message->setSubject($subject);
					$message->setEncoding("UTF-8");
					
					if ($smtp_options) {
						$transport = new Mail\Transport\Smtp();
						$_auth = new Mail\Transport\SmtpOptions(array (
								'name' => $smtp_options ['server'],
								'host' => $smtp_options ['server'],
								'port' => $smtp_options ['port'],
								'connection_class' => 'login',
								'connection_config' => array (
										'username' => $smtp_options ['username'],
										'password' => $smtp_options ['password'],
										'ssl' => 'tls' 
								) 
						));
						$transport->setOptions($_auth);
					} else {
						$transport = new Mail\Transport\Sendmail();
					}
					$transport->send($message);
				} else {
					return $this->respondError(new \Exception('No Lead Data could be found.', 404));
				}
			} else {
				return $this->respondError(new \Exception('One or more email settings were missing.', 400));
			}
		} else {
			return $this->respondError(new \Exception('API Options were not set or could not be retrieved.', 400));
		}
		return $this->respondSuccess([ 
				'message' => 'Email sent to ' . implode(", ", $address_to),
				'event' => 'Email Sent',
				'addressTo' => implode(", ", $address_to) 
		]);
	}

	/*
	 * (non-PHPdoc)
	 * @see \Email\Service\AbstractEmailService::getOptions()
	 */
	public function getOptions($id, $service = 'Email')
	{
		$results = [ ];
		$em = $this->getEntityManager();
		
		/* @var $lb \Doctrine\ORM\QueryBuilder */
		$lb = $em->createQueryBuilder()
			->select('ss')
			->from('Api\Entity\ApiSetting', 'ss')
			->leftJoin('ss.account', 'ac')
			->leftJoin('ss.api', 'ap')
			->leftJoin('ac.leads', 'ld')
			->where('ld.id = :id')
			->andWhere('ap.name = :api')
			->setParameters([ 
				'api' => $service,
				'id' => $id 
		]);
		
		$localSettings = $lb->getQuery()
			->getResult();
		
		/* @var $gb \Doctrine\ORM\QueryBuilder */
		$gb = $em->createQueryBuilder();
		$gb->add('select', 'op')
			->add('from', 'Api\Entity\ApiOption op')
			->innerJoin('op.api', 'ap')
			->where('op.scope = :scope')
			->andWhere('ap.name = :api')
			->setParameters([ 
				'api' => $service,
				'scope' => 'global' 
		]);
		$globalSettings = $gb->getQuery()
			->getResult();
		
		if ($globalSettings) {
			foreach ( $globalSettings as $option ) {
				$results ['global'] [$option->getOption()] [] = $option->getValue();
			}
		}
		
		if ($localSettings) {
			foreach ( $localSettings as $setting ) {
				$results ['local'] [$setting->getApiOption()
					->getOption()] [] = $setting->getApiValue();
			}
		}
		if ($service == 'Email') {
			$config = $this->getServiceLocator()
				->get('Config');
			$smtp_options = isset($config ['User'] ['smtp']) ? $config ['User'] ['smtp'] : false;
			if ($smtp_options) {
				$results ['global'] ['smtp'] = $smtp_options;
			}
		}
		return $results;
	}

	/*
	 * (non-PHPdoc)
	 * @see \Email\Service\AbstractEmailService::getData()
	 */
	public function getData($id)
	{
		$data = $this->getLead($id);
		if ($data) {
			$html = $this->getBody($data, true);
			$text = $this->getBody($data, false);
			return compact('html', 'text');
		}
		return false;
	}

	/*
	 * (non-PHPdoc)
	 * @see \Email\Service\AbstractEmailService::logEvent()
	 */
	public function logEvent($event)
	{
		$this->getEventManager()
			->trigger($event, $this->getServiceEvent());
	}

	/*
	 * (non-PHPdoc)
	 * @see \Email\Service\AbstractEmailService::respond()
	 */
	public function respond($data = null)
	{
		return [ 
				'email' => $data 
		];
	}

	/*
	 * (non-PHPdoc)
	 * @see \Email\Service\AbstractEmailService::respondError()
	 */
	public function respondError(\Exception $e)
	{
		$this->getServiceEvent()
			->setIsError(true);
		$this->getServiceEvent()
			->setMessage($e->getMessage());
		$this->getServiceEvent()
			->setResult($e->getTraceAsString());
		$this->logEvent('RuntimeError');
		return $this->respond($this->errorResponse->errorHandler($e->getCode(), $e->getMessage(), null, $e->getTraceAsString()));
	}

	/*
	 * (non-PHPdoc)
	 * @see \Email\Service\AbstractEmailService::respondSuccess()
	 */
	public function respondSuccess($result)
	{
		$this->getServiceEvent()
			->setMessage($result ['message'] ?  : 'Unknown Response')
			->setOutcome(1)
			->setParam('addressTo', $result ['addressTo']);
		$this->logEvent('SendMail.post');
		return $this->respond($this->errorResponse->successOperation($result));
	}
}

?>