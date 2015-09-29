<?php
namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use LosBase\Entity\EntityManagerAwareTrait;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Mvc\Router\SimpleRouteStack;

/**
 *
 * @author arstropica
 *        
 */
class EntityNavSelect extends AbstractHelper implements ServiceLocatorAwareInterface
{
	use EntityManagerAwareTrait;

	protected $serviceLocator;

	protected $parentLocator;

	protected $routeMatch;

	protected $actions;

	/**
	 *
	 * @var SimpleRouteStack
	 */
	protected $router;

	public function __construct ($routeMatch)
	{
		$this->routeMatch = $routeMatch;
		$this->actions = [
				'view',
				'edit',
				'add',
				'index',
				'list',
				'submit',
				'send',
				'process',
				'delete',
				'confirm'
		];
	}

	public function __invoke ($action = 'view')
	{
		$this->parentLocator = $this->getServiceLocator()->getServiceLocator();
		$this->router = $this->parentLocator->get('Router');
		$p = $this->routeMatch->getParams();
		$c = $p['controller'];
		$n = $this->getModuleName($c);
		$v = $this->getView();
		$r = $this->routeMatch->getMatchedRouteName();
		$em = $this->getEntityManager();
		$objRepository = $em->getRepository($this->getEntityClass($p['controller']));
		
		$i = new InputFilter();
		$i->add(
				array(
						'name' => 'entitynav',
						'required' => true,
						'filters' => array(
								array(
										'name' => 'Zend\Filter\StringTrim'
								)
						)
				));
		$form = new Form('entitynav');
		$form->setAttribute('method', 'POST');
		$form->setAttribute('action', $v->url('navigation'));
		
		$form->add(
				array(
						'name' => 'params[id]',
						'type' => 'DoctrineModule\Form\Element\ObjectSelect',
						'required' => false,
						'allow_empty' => true,
						'filters' => array(
								array(
										'name' => 'Zend\Filter\StringTrim'
								)
						),
						'options' => array(
								// 'label' => 'Switch ' . $n . ': ',
								'label_attributes' => array(
										'class' => 'sr-only'
								),
								'empty_option' => 'Switch ' . $n . '(s)',
								'object_manager' => $em,
								'target_class' => $this->getEntityClass($c),
								'is_method' => true,
								'find_method' => array(
										'name' => 'findAll'
								)
						),
						'attributes' => array(
								'id' => strtolower($n) . 'id',
								'onChange' => 'if (this.value) this.form.submit()',
								'value' => isset($p['id']) ? $p['id'] : ""
						)
				));
		
		$form->add(
				array(
						'name' => 'controller',
						'type' => 'hidden',
						'attributes' => array(
								'value' => $p['controller']
						)
				));
		$form->add(
				array(
						'name' => 'params[action]',
						'type' => 'hidden',
						'attributes' => array(
								'value' => $this->hasAction($action) ? $action : $p['action']
						)
				));
		$form->add(array(
				'name' => 'route',
				'type' => 'hidden',
				'attributes' => array(
						'value' => $this->findRoute($action)
				)
		));
		$form->setInputFilter($i);
		echo $this->helperExists('losForm') ? $v->losForm($form, false) : $v->form($form);
	}

	protected function hasAction ($action = false)
	{
		$matchedRoute = $this->routeMatch->getMatchedRouteName();
		$result = true;
		$route = $this->router;
		if ($action) {
			$parts = explode('/', $matchedRoute);
			if (count($parts) > 1 && in_array(end($parts), $this->actions)) {
				array_pop($parts);
			}
			foreach ($parts as $part) {
				if ($route->hasRoute($part)) {
					$route = $route->getRoute($part);
				} else {
					$route = false;
					$result = false;
					break;
				}
			}
			if ($route && $result)
				$result = $route->hasRoute($action);
		}
		return $result;
	}

	protected function findRoute ($action = false)
	{
		$newRoute = $matchedRoute = $this->routeMatch->getMatchedRouteName();
		if ($action) {
			if ($this->hasAction($action)) {
				$parts = explode('/', $matchedRoute);
				if (count($parts) > 1 && in_array(end($parts), $this->actions)) {
					array_pop($parts);
				}
				$newRoute = implode('/', $parts) . '/' . $action;
			}
		}
		return $newRoute;
	}

	protected function getModuleName ($c)
	{
		$module_array = explode('\\', $c);
		
		return $module_array[0];
	}

	protected function getEntityClass ($c)
	{
		$module = $this->getModuleName($c);
		
		return "$module\Entity\\$module";
	}

	protected function helperExists ($name)
	{
		return (bool) $this->getView()
			->getHelperPluginManager()
			->get($name, false);
	}

	public function getEntityManager ()
	{
		if (null === $this->em) {
			$this->em = $this->getServiceLocator()
				->getServiceLocator()
				->get('doctrine.entitymanager.orm_default');
		}
		
		return $this->em;
	}

	public function setServiceLocator (ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}

	public function getServiceLocator ()
	{
		return $this->serviceLocator;
	}
}

?>