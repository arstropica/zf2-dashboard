<?php
namespace Application\View\Helper;
use Zend\Mvc\Controller\Plugin\FlashMessenger as ZendFlash;
use Zend\View\Helper\AbstractHelper;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\RequestInterface;

class FlashMessenger extends AbstractHelper
{

	/**
	 *
	 * @var \Zend\Mvc\Controller\Plugin\FlashMessenger
	 */
	protected $flashMessenger;

	/**
	 *
	 * @var array
	 */
	protected $namespaces = array(
			'info' => 'default',
			'danger' => 'error',
			'success' => 'success',
			'info' => 'info',
			'warning' => 'warning'
	);

	protected $request;

	protected $event;

	public function __construct (RequestInterface $request, MvcEvent $event)
	{
		$this->request = $request;
		$this->event = $event;
	}

	public function setFlashMessenger (ZendFlash $flashMessenger)
	{
		$this->flashMessenger = $flashMessenger;
		
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function __invoke ()
	{
		if ($this->request->getQuery('msg', false) &&
				 $this->flashMessenger->hasMessages()) {
			$messages = $this->flashMessenger->getMessages();
			foreach ($messages as $message) {
				$this->flashMessenger->addInfoMessage($message);
			}
		}
		$messageString = '';
		foreach ($this->namespaces as $class => $ns) {
			$this->flashMessenger->setNamespace($ns);
			$messages = $this->flashMessenger->getMessages();
			if ($this->flashMessenger->hasCurrentMessages()) {
				$messages += $this->flashMessenger->getCurrentMessages();
				$this->flashMessenger->clearCurrentMessages();
			}
			
			if (count($messages) > 0) {
				// Twitter bootstrap message box
				$messageString .= sprintf(
						'<div class="container">
                        <div class="alert alert-%s alert-dismissable fade in">
                            <button data-dismiss="alert" class="close" aria-hidden="true" type="button">x</button>
                            %s
                        </div>
                    </div>', $class, 
						implode('<br />', $messages));
			}
		}
		
		return $messageString;
	}
}