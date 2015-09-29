<?php
namespace Email\Service\Factory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Email\Service\SendMailService;

/**
 *
 * @author arstropica
 *        
 */
class SendMailServiceFactory implements FactoryInterface
{

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Zend\ServiceManager\FactoryInterface::createService()
	 *
	 */
	public function createService (ServiceLocatorInterface $service)
	{
		try {
			return new SendMailService($service);
		} catch (\Exception $e) {
			return false;
		}
	}
}

?>