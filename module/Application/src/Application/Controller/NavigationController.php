<?php
namespace Application\Controller;
use Zend\Mvc\Controller\AbstractActionController;

/**
 *
 * @author arstropica
 *        
 */
class NavigationController extends AbstractActionController
{

	public function indexAction ()
	{
		$p = $this->getRequest()->getPost()->toArray();
		return $this->redirect()->toRoute($p['route'], $p['params']);
	}
}

?>