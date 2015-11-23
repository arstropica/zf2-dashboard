<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Account for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Account\Controller;

use Application\Controller\AbstractCrudController;
use Zend\Paginator\Paginator;
use Doctrine\ORM\QueryBuilder as Builder;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use LosBase\ORM\Tools\Pagination\Paginator as LosPaginator;
use DoctrineORMModule\Stdlib\Hydrator\DoctrineEntity as DoctrineHydrator;
use LosBase\Validator\NoOtherEntityExists;
use Zend\Stdlib\ResponseInterface as Response;
use Account\Utility\IdGenerator;
use Account\Entity\Account;

class AccountController extends AbstractCrudController
{

    protected $defaultSort = 'name';

    protected $defaultOrder = 'asc';

    protected $defaultPageSize = 10;

    protected $paginatorRange = 5;

    protected $uniqueField = null;

    protected $uniqueEntityMessage = null;

    protected $successAddMessage = 'The Account was successfully added.';

    protected $successEditMessage = 'The Account(s) were successfully edited.';

    protected $successDeleteMessage = 'The Account(s) were successfully deleted.';

    protected $errorEditMessage = 'There was a problem editing your Account(s).';

    protected $errorDeleteMessage = 'There was a problem deleting your Account(s).';

    public function listAction()
    {
        $page = $this->getRequest()->getQuery('page', 0);
        $limit = $this->getRequest()->getQuery('limit', $this->defaultPageSize);
        $sort = $this->getRequest()->getQuery('sort', $this->defaultSort);
        $order = $this->getRequest()->getQuery('order', $this->defaultOrder);
        
        if (empty($sort)) {
            $sort = $this->defaultSort;
        }
        
        $offset = $limit * $page - $limit;
        if ($offset < 0) {
            $offset = 0;
        }
        
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $this->buildQuery($sort, $limit, $order, $offset);
        
        $paginator = new Paginator(new DoctrinePaginator(new LosPaginator($qb, false)));
        $paginator->setDefaultItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange($this->paginatorRange);
        
        $ui = [
            'table' => [
                "name" => [
                    "col" => 2,
                    "label" => "Name",
                    "sort" => true
                ],
                "description" => [
                    "col" => 4,
                    "label" => "Description",
                    "sort" => true
                ],
                "api" => [
                    "col" => 2,
                    "label" => "APIs",
                    "sort" => false
                ],
                "leads" => [
                    "col" => 2,
                    "label" => "# Leads",
                    "sort" => false
                ]
            ]
        ];
        
        $filters = $this->getFilterForm($this->params()
            ->fromQuery());
        $post = $this->params()->fromPost();
        
        $redirectUrl = $this->url()->fromRoute($this->getActionRoute(), [], true);
        $prg = $this->prg($redirectUrl, true);
        
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg !== false) {
            return $this->confirmBatchDelete($ui, $prg);
        }
        $form = $this->getListForm($paginator);
        $pager = $this->getPagerForm($limit);
        return [
            'paginator' => $paginator,
            'sort' => $sort,
            'order' => $order,
            'page' => $page,
            'query' => $this->params()->fromQuery(),
            'form' => $form,
            'filters' => $filters,
            'ui' => $ui,
            'pager' => $pager
        ];
    }

    public function viewAction()
    {
        $id = $this->getEvent()
            ->getRouteMatch()
            ->getParam('id', 0);
        
        $em = $this->getEntityManager();
        $objRepository = $em->getRepository($this->getEntityClass());
        $entity = $objRepository->find($id);
        
        return [
            'entity' => $entity
        ];
    }

    public function addAction()
    {
        if (method_exists($this, 'getAddForm')) {
            $form = $this->getAddForm();
        } else {
            $form = $this->getForm();
        }
        
        $classe = $this->getEntityClass();
        $entity = new $classe();
        
        $this->getEventManager()->trigger('getForm', $this, [
            'form' => $form,
            'entityClass' => $this->getEntityClass(),
            'id' => 0,
            'entity' => $entity
        ]);
        
        $form->bind($entity);
        
        $redirectUrl = $this->url()->fromRoute($this->getActionRoute(), [], true);
        $prg = $this->fileprg($form, $redirectUrl, true);
        
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            if ($form->has('guid')) {
                $guid = $form->get('guid')->getValue();
                if (! $guid) {
                    $guid = IdGenerator::generate();
                    $form->get('guid')->setValue($guid);
                }
            }
            
            $this->getEventManager()->trigger('getForm', $this, [
                'form' => $form,
                'entityClass' => $this->getEntityClass(),
                'entity' => $entity
            ]);
            
            return [
                'entityForm' => $form,
                'entity' => $entity
            ];
        }
        
        $this->createServiceEvent()
            ->setEntityClass($this->getEntityClass())
            ->setDescription("Account Created");
        
        $this->getEventManager()->trigger('add', $this, [
            'form' => $form,
            'entityClass' => $this->getEntityClass(),
            'entity' => $entity
        ]);
        
        $savedEntity = $this->getEntityService()->save($form, $entity);
        
        if ($savedEntity && $savedEntity instanceof Account) {
            $name = $savedEntity->getName();
            $this->getServiceEvent()
                ->setEntityId($savedEntity->getId())
                ->setMessage("Account : {$name} was created.");
            
            $this->logEvent("AddAction.post");
        } else {
            return [
                'entityForm' => $form,
                'entity' => $entity
            ];
        }
        
        $this->flashMessenger()->addSuccessMessage($this->getServiceLocator()
            ->get('translator')
            ->translate($this->successAddMessage));
        
        if ($this->needAddOther($form)) {
            $action = 'add';
        } else {
            $action = 'list';
        }
        
        return $this->redirect()->toRoute($this->getActionRoute($action), [], true);
    }

    public function editAction()
    {
        if (method_exists($this, 'getEditForm')) {
            $form = $this->getEditForm();
        } else {
            $form = $this->getForm();
        }
        
        $id = $this->getEvent()
            ->getRouteMatch()
            ->getParam('id', 0);
        
        $form->add([
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id',
            'attributes' => [
                'id' => 'id',
                'value' => $id
            ],
            'filters' => [
                [
                    'name' => 'Int'
                ]
            ],
            'validators' => [
                [
                    'name' => 'Digits'
                ]
            ]
        ]);
        
        $em = $this->getEntityManager();
        $objRepository = $em->getRepository($this->getEntityClass());
        $entity = $objRepository->find($id);
        
        $this->getEventManager()->trigger('getForm', $this, [
            'form' => $form,
            'entityClass' => $this->getEntityClass(),
            'id' => $id,
            'entity' => $entity
        ]);
        
        $form->bind($entity);
        
        $redirectUrl = $this->url()->fromRoute($this->getActionRoute(), [], true);
        $prg = $this->fileprg($form, $redirectUrl, true);
        
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $this->getEventManager()->trigger('getForm', $this, [
                'form' => $form,
                'entityClass' => $this->getEntityClass(),
                'id' => $id,
                'entity' => $entity
            ]);
            
            return [
                'entityForm' => $form,
                'entity' => $entity
            ];
        }
        
        $this->createServiceEvent()
            ->setEntityId($id)
            ->setEntityClass($this->getEntityClass())
            ->setDescription("Account Edited");
        
        $this->getEventManager()->trigger('edit', $this, [
            'form' => $form,
            'entityClass' => $this->getEntityClass(),
            'id' => $id,
            'entity' => $entity
        ]);
        
        $savedEntity = $this->getEntityService()->save($form, $entity);
        
        if ($savedEntity && $savedEntity instanceof Account) {
            $name = $savedEntity->getName();
            $this->getServiceEvent()->setMessage("Account : {$name} was edited.");
            
            $this->logEvent("EditAction.post");
        } else {
            return [
                'entityForm' => $form,
                'entity' => $entity
            ];
        }
        
        $this->flashMessenger()->addSuccessMessage($this->getServiceLocator()
            ->get('translator')
            ->translate($this->successEditMessage));
        
        return $this->redirect()->toRoute($this->getActionRoute('list'), [], true);
    }

    public function getForm($entityClass = null)
    {
        $form = parent::getForm($entityClass);
        
        if ($form) {
            $entityClass = $entityClass ?  : $this->getEntityClass();
            $hydrator = new DoctrineHydrator($this->getEntityManager(), $entityClass);
            $form->setHydrator($hydrator);
            if ($form->has('submit')) {
                $form->get('submit')->setLabel('Save');
            }
            if ($form->has('cancelar')) {
                $form->get('cancelar')
                    ->setLabel('Cancel')
                    ->setName('cancel');
            }
        }
        return $form;
    }

    protected function getFilterForm($data = array())
    {
        $sl = $this->getServiceLocator();
        $form = $sl->get('FormElementManager')->get('Account\Form\FilterForm');
        $form->setInputFilter($form->getInputFilter());
        if ($data) {
            $form->setData($data);
            if (! $form->isValid()) {
                $form->setData(array());
            }
        }
        return $form;
    }

    protected function getListForm(Paginator $paginator, $data = [])
    {
        $sl = $this->getServiceLocator();
        $form = $sl->get('FormElementManager')->get('Account\Form\ListForm');
        $form->setName('accountbatchform');
        // $form->setAttribute('action',
        // $this->url()->fromRoute('account/confirm', [], false));
        if ($paginator->count() > 0) {
            // Batch Form
            
            foreach ($paginator as $entity) {
                $cbx = new \Zend\Form\Element\Checkbox("sel[" . $entity->getId() . "]");
                $form->add($cbx);
            }
        }
        if ($data) {
            $form->setData($data);
            if (! $form->isValid()) {
                $form->setData(array());
            }
        }
        return $form;
    }

    protected function getDeleteForm(Paginator $paginator, $data = [])
    {
        $sl = $this->getServiceLocator();
        $form = $sl->get('FormElementManager')->get('Account\Form\ListForm');
        $form->setName('accountbatchdelform');
        $form->remove('submit')->addConfirm();
        if ($paginator->count() > 0) {
            // Batch Form
            
            foreach ($paginator as $entity) {
                $hdn = new \Zend\Form\Element\Hidden("sel[" . $entity->getId() . "]");
                $hdn->setValue('1');
                $form->add($hdn);
            }
        }
        if ($data) {
            $form->setData($data);
            if (! $form->isValid()) {
                $form->setData(array());
            }
        }
        return $form;
    }

    public function handleSearch(Builder $qb)
    {
        $query = $this->getRequest()->getQuery();
        $filters = [
            'description'
        ];
        if ($query) {
            $where = [];
            $params = [];
            foreach ($filters as $condition) {
                if (isset($query[$condition]) && "" !== $query[$condition]) {
                    switch ($condition) {
                        case 'description':
                            $where['description'] = "%{$query[$condition]}%";
                            $qb->andWhere("e.description LIKE :description");
                            $qb->orWhere("e.name LIKE :description");
                            break;
                    }
                }
            }
            if ($where) {
                foreach ($where as $key => $value) {
                    $qb->setParameter($key, $value);
                }
            }
        }
        return $qb;
    }

    public function confirmBatchDelete($ui, $post = [])
    {
        $sort = $this->getRequest()->getQuery('sort', $this->defaultSort);
        $order = $this->getRequest()->getQuery('order', $this->defaultOrder);
        
        if (empty($sort)) {
            $sort = $this->defaultSort;
        }
        
        $accounts = isset($post['sel']) ? $post['sel'] : false;
        
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->add('select', 'e')
            ->add('from', $this->getEntityClass() . ' e')
            ->orderBy('e.' . $sort, $order);
        
        if ($accounts) {
            foreach ($accounts as $account_id => $one) {
                if ($one) {
                    $qb->orWhere('e.id = ' . $account_id);
                }
            }
        } else {
            return [];
        }
        
        $paginator = new Paginator(new DoctrinePaginator(new LosPaginator($qb, false)));
        $form = $this->getDeleteForm($paginator);
        
        if (isset($post['submit'])) {
            $this->flashMessenger()->addWarningMessage("Note: Any associated leads will be orphaned.");
            return [
                'paginator' => $paginator,
                'form' => $form,
                'ui' => $ui
            ];
        } elseif (isset($post['confirm'])) {
            $result = true;
            if ($accounts) {
                foreach ($accounts as $account_id => $one) {
                    if ($one) {
                        $result = $this->delete($account_id) ? $result : false;
                    }
                }
                if (! $result) {
                    $this->flashMessenger()->addErrorMessage($this->getServiceLocator()
                        ->get('translator')
                        ->translate($this->errorDeleteMessage));
                } else {
                    $this->flashMessenger()->addSuccessMessage($this->getServiceLocator()
                        ->get('translator')
                        ->translate($this->successDeleteMessage));
                }
            }
        }
        
        return $this->redirect()->toRoute($this->getActionRoute('list'), [], true);
    }

    protected function buildQuery($sort, $limit, $order, $offset)
    {
        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->add('select', 'e')
            ->add('from', $this->getEntityClass() . ' e')
            ->orderBy('e.' . $sort, $order)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        
        return $this->handleSearch($qb);
    }

    protected function delete($id)
    {
        $result = false;
        
        $em = $this->getEntityManager();
        $objRepository = $em->getRepository($this->getEntityClass());
        $entity = $objRepository->find($id);
        
        if (($leads = $entity->getLeads(true)) == true) {
            $entity->removeLeads($leads);
            try {
                $em = $this->getEntityManager();
                $em->persist($entity);
                $em->flush();
            } catch (\Exception $e) {
                return false;
            }
        }
        
        if ($this->getEntityService()->delete($entity)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }

    protected function editEntry($id, $data = [])
    {
        if (! $data)
            return false;
        
        $form = $this->getForm();
        
        $form->remove('csrf');
        
        if ($this->uniqueField !== null) {
            $validator = new NoOtherEntityExists([
                'object_repository' => $this->getEntityManager()->getRepository($this->getEntityClass()),
                'fields' => $this->uniqueField,
                'id' => $id
            ]);
            if ($this->uniqueEntityMessage !== null) {
                $validator->setMessage($this->uniqueEntityMessage, 'objectFound');
            }
            $form->getInputFilter()
                ->get($this->uniqueField)
                ->getValidatorChain()
                ->attach($validator);
        }
        
        $form->add([
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id',
            'attributes' => [
                'id' => 'id',
                'value' => $id
            ],
            'filters' => [
                [
                    'name' => 'Int'
                ]
            ],
            'validators' => [
                [
                    'name' => 'Digits'
                ]
            ]
        ]);
        
        $em = $this->getEntityManager();
        $objRepository = $em->getRepository($this->getEntityClass());
        $entity = $objRepository->find($id);
        
        $form->bind($entity);
        
        $form->setData($data);
        
        $savedEntity = $this->getEntityService()->save($form, $entity);
        
        if ($savedEntity && $savedEntity instanceof Account) {
            $name = $savedEntity->getName();
            $this->createServiceEvent()
                ->setEntityId($id)
                ->setEntityClass($this->getEntityClass())
                ->setDescription("Account Edited")
                ->setMessage("Account : " . $name . " was edited.");
            $this->logEvent("EditAction.post");
        }
        
        return $savedEntity ? true : false;
    }

    protected function logEvent($event)
    {
        $this->getEventManager()->trigger($event, $this->getServiceEvent());
    }

    protected function logError(\Exception $e, $result = [])
    {
        $this->getServiceEvent()->setIsError(true);
        $this->getServiceEvent()->setMessage($e->getMessage());
        if ($result) {
            $this->getServiceEvent()->setResult(print_r($result, true));
        } else {
            $this->getServiceEvent()->setResult($e->getTraceAsString());
        }
        $this->logEvent('RuntimeError');
    }
}
