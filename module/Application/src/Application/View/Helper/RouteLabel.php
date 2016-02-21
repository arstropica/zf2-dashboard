<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Navigation\Page\Mvc as Page;
use LosBase\Entity\EntityManagerAwareTrait;

/**
 *
 * @author arstropica
 *        
 */
class RouteLabel extends AbstractHelper implements ServiceLocatorAwareInterface {
	use EntityManagerAwareTrait;
	
	protected $serviceLocator;

	public function __invoke(Page $page)
	{
		if (isset($page->dynamic) && $page->dynamic) {
			$p = $page->route_match->getParams();
			if (isset($p ['id'])) {
				$id = $p ['id'];
				$route = $page->route;
				$sl = $this->getServiceLocator()
					->getServiceLocator();
				$em = $this->getEntityManager();
				$objRepository = $em->getRepository($this->getEntityClass($p ['controller']));
				$entity = $objRepository->find($id);
				switch ($route) {
					case 'account/application' :
						$page->params = [ 
								'id' => $id 
						];
						$page->setLabel($entity->getName());
						break;
					case 'report/application' :
						$page->params = [ 
								'id' => $id 
						];
						$page->setLabel($entity->getName());
						break;
					case 'lead/search' :
						$page->params = [ 
								'id' => $id 
						];
						if ($entity) {
							$page->setLabel($entity->getName());
						}
						break;
				}
			}
		}
	}

	protected function getModuleName($c)
	{
		$module_array = explode('\\', $c);
		
		return $module_array [0];
	}

	protected function getEntityClass($c)
	{
		$module = $this->getModuleName($c);
		
		return "$module\Entity\\$module";
	}

	public function getEntityManager()
	{
		if (null === $this->em) {
			$this->em = $this->getServiceLocator()
				->getServiceLocator()
				->get('doctrine.entitymanager.orm_default');
		}
		
		return $this->em;
	}

	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}

	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}
}

?>