<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use LosBase\Entity\EntityManagerAwareTrait;

class IndexController extends AbstractActionController
{
	use EntityManagerAwareTrait;

	public function indexAction ()
	{
		return $this->redirect()->toRoute('dashboard');
	}

	public function dashboardAction ()
	{
		/**
		 *
		 * @var $entityService \my\Service\EntitynameService
		 */
		$leadService = $this->getServiceLocator()->get(
				'Application\Service\Lead');
		$accountService = $this->getServiceLocator()->get(
				'Application\Service\Account');
		$eventService = $this->getServiceLocator()->get(
				'Application\Service\Event');
		
		// A query that finds all stuff
		// $allEntities = $entityService->findAll();
		
		// A query that finds an ID
		// $idEntity = $entityService->find(1);
		
		// A query that finds entities based on a Query
		$leads = $leadService->findByQuery(
				function  ($queryBuilder)
				{
					/**
					 *
					 * @var $queryBuilder\Doctrine\DBAL\Query\QueryBuilder
					 */
					return $queryBuilder->setMaxResults(10)
						->orderBy('entity.timecreated', 'DESC');
				});
		
		$accounts = $accountService->findByQuery(
				function  ($queryBuilder)
				{
					/**
					 *
					 * @var $queryBuilder\Doctrine\DBAL\Query\QueryBuilder
					 */
					return $queryBuilder->setMaxResults(10)
						->orderBy('entity.id', 'DESC');
				});
		
		$events = $eventService->findByQuery(
				function  ($queryBuilder)
				{
					/**
					 *
					 * @var $queryBuilder\Doctrine\DBAL\Query\QueryBuilder
					 */
					return $queryBuilder->setMaxResults(10)
						->orderBy('entity.id', 'DESC');
				});
		
		return new ViewModel(
				[
						'leads' => $leads,
						'accounts' => $accounts,
						'events' => $events
				]);
	}
}
