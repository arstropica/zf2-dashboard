<?php
namespace Application\Event\Listener;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\SharedEventManager;
use Event\Entity\Event;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use Application\Provider\EntityManagerAwareTrait;
// use Event\Entity\ApiEvent;
use Event\Entity\AccountApiEvent;
use Event\Entity\AccountEvent;
use Application\Event\ServiceEvent;
use Event\Entity\ErrorEvent;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Hydrator\Strategy\MaybeSerializableStrategy;

/**
 *
 * @author arstropica
 *        
 */
abstract class AggregateAbstractListener implements ListenerAggregateInterface
{
	
	use EntityManagerAwareTrait, ServiceLocatorAwareTrait;

	/**
	 *
	 * @var \Zend\Stdlib\CallbackHandler[]
	 */
	protected $listeners = array();

	/**
	 *
	 * @var \Event\Entity\Event
	 */
	protected $event;

	public function __construct (ServiceLocatorInterface $serviceLocator)
	{
		$this->setServiceLocator($serviceLocator);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\EventManager\ListenerAggregateInterface::detach()
	 */
	public function detach (EventManagerInterface $events)
	{
		foreach ($this->listeners as $index => $listener) {
			if ($events->detach($listener)) {
				unset($this->listeners[$index]);
			}
		}
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\EventManager\ListenerAggregateInterface::attach()
	 *
	 */
	public function attach (EventManagerInterface $events)
	{}

	public function _attachStateful (SharedEventManager $sm, $target, $method)
	{
		$i = 100;
		foreach ([
				'pre' => 'pre',
				'' => 'on',
				'post' => 'post'
		] as $prefix => $state) {
			try {
				if (method_exists($this, $prefix . $method)) {
					
					$this->listeners[] = $sm->attach($target, 
							"{$method}.{$state}", 
							array(
									$this,
									$prefix . $method
							), $i);
				}
			} catch (\Exception $e) {}
			$i -= 100;
		}
	}

	protected function dispatch ($entity, $event, $data = [])
	{
		$valid = false;
		$base = $this->getEvent($data['action'], $data['message']);
		$eventName = basename(str_replace('\\', '/', get_class($event)));
		$entityName = is_object($entity) ? basename(
				str_replace('\\', '/', get_class($entity))) : false;
		
		if ($base && $event) {
			$event->setEvent($base);
			
			switch ($entityName) {
				case 'Account':
					switch ($eventName) {
						case 'AccountEvent':
							$event->setAccount($entity);
							$valid = true;
							break;
						case 'ErrorEvent':
							$valid = true;
							break;
					}
					break;
				case 'Api':
					switch ($eventName) {
						case 'ApiEvent':
							$event->setApi($entity);
							$valid = true;
							break;
						case 'ErrorEvent':
							$valid = true;
							break;
					}
					break;
				case 'Lead':
				default:
					switch ($eventName) {
						case 'TenStreetApiEvent':
						case 'EmailApiEvent':
							$account = $entity->getAccount();
							if ($account) {
								$name = $eventName == 'EmailApiEvent' ? 'Email' : 'Tenstreet';
								$api = $account->findApi($name);
								if ($api) {
									$accountApiEvent = new AccountApiEvent();
									$accountApiEvent->setEvent($base)
										->setApi($api)
										->setAccount($account);
									$this->dispatch($entity, $accountApiEvent, 
											$data);
									// $apiEvent = new ApiEvent();
									// $apiEvent->setEvent($base)->setApi($api);
									// $this->dispatch($entity, $apiEvent,
									// $data);
									$valid = true;
								}
								$event->setAccount($account);
							}
							break;
						case 'ErrorEvent':
							$valid = true;
							break;
						case 'LeadEvent':
							$event->setLead($entity);
							$account = $entity->getAccount();
							if ($account) {
								$accountEvent = new AccountEvent();
								$accountEvent->setEvent($base)->setAccount(
										$account);
								$this->dispatch($entity, $accountEvent, $data);
								$valid = true;
							}
							$valid = true;
							break;
						case 'AccountEvent':
							$account = $entity->getAccount();
							if ($account) {
								$event->setAccount($account);
								$valid = true;
							}
							break;
					}
					break;
			}
			if ($valid) {
				try {
					$hydrator = new DoctrineHydrator($this->getEntityManager(), 
							get_class($event));
					
					foreach ([
							'response',
							'trace'
					] as $serializable) {
						if (method_exists($event, 
								'get' . ucwords($serializable))) {
							$hydrator->addStrategy($serializable, 
									new MaybeSerializableStrategy());
						}
					}
					
					$event = $hydrator->hydrate($data, $event);
					$em = $this->getEntityManager();
					$em->persist($event);
					$em->flush();
				} catch (\Exception $e) {
					// fail silently
					return false;
				}
				return true;
			}
		}
		
		return false;
	}

	/**
	 * On Error Listener
	 *
	 * @param ServiceEvent $e        	
	 *
	 * @return boolean
	 */
	public function OnError (ServiceEvent $e)
	{
		$data = [];
		$data['action'] = $e->getDescription();
		$data['message'] = $e->getMessage();
		$data['trace'] = $e->getResult();
		
		return $this->dispatch(null, new ErrorEvent(), $data);
	}

	/**
	 *
	 * @param Event $event        	
	 * @return AggregateAbstractListener
	 */
	protected function setEvent (Event $event)
	{
		$this->event = $event;
		
		return $this;
	}

	/**
	 *
	 * @return AggregateAbstractListener
	 */
	protected function clearEvent ()
	{
		$this->event = null;
		
		return $this;
	}

	/**
	 *
	 * @param string $action        	
	 * @param string $message        	
	 * @return \Event\Entity\Event
	 */
	protected function getEvent ($action = null, $message = null)
	{
		if (! $this->event) {
			if (! $action) {
				$action = 'Unknown ' . get_called_class() . ' Event';
			}
			
			if (! $message) {
				$message = 'Unknown ' . get_called_class() . ' Event';
			}
			
			$data = [
					'event' => $action,
					'occurred' => new \DateTime('now'),
					'message' => $message
			];
			try {
				$event = new Event();
				$hydrator = new DoctrineHydrator($this->getEntityManager(), 
						get_class($event));
				$event = $hydrator->hydrate($data, $event);
				$entityManager = $this->getEntityManager();
				$entityManager->persist($event);
				$entityManager->flush();
				$this->event = $event;
			} catch (\Exception $e) {
				// fail silently
				return false;
			}
		}
		
		return $this->event;
	}
}
