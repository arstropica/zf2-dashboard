<?php
namespace AccountTest\Controller;

// require_once 'module/Account/src/Account/Controller/AccountController.php';
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Account\Controller\AccountController;
# use Doctrine\ORM\EntityManager;
# use Doctrine\ORM\EntityRepository;
# use Doctrine\ORM\QueryBuilder;
# use Doctrine\ORM\AbstractQuery;
use Account\Entity\Account;

/**
 * AccountController test case.
 */
class AccountControllerTest extends AbstractHttpControllerTestCase
{

    /**
     *
     * @var AccountController
     */
    private $accountController;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $appConfig = include 'config/application.config.php';
        $this->setApplicationConfig($appConfig);
        parent::setUp();
        
        $user = new \MockUser($this->getApplication()->getServiceManager());
        
        $account = new Account();
        
        $account->setId(1)
            ->setActive(1)
            ->setDescription('Test Account')
            ->setGuid('24234rr2r2r')
            ->setName('Test Account');
        
        $em = $this->getMock('EntityManager', array(
            'getRepository',
            'getClassMetadata',
            'persist',
            'flush',
            'find'
        ), array(), '', false);
        
        $em->expects($this->any())
            ->method('find')
            ->will($this->returnValue($account));
        
        $this->accountController = new AccountController(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated AccountControllerTest::tearDown()
        $this->accountController = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests List action can be accessed.
     */
    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/account');
        $this->assertResponseStatusCode(200);
        
        $this->assertModuleName('account');
        $this->assertControllerName('Account\Controller\Account');
        $this->assertControllerClass('AccountController');
        $this->assertMatchedRouteName('account');
    }

    /**
     * Tests AccountController->listAction()
     */
    public function testListAction()
    {
        // TODO Auto-generated AccountControllerTest->testListAction()
        $this->markTestIncomplete("listAction test not implemented");
        
        $this->accountController->listAction(/* parameters */);
    }

    /**
     * Tests AccountController->viewAction()
     */
    public function testViewAction()
    {
        // TODO Auto-generated AccountControllerTest->testViewAction()
        $this->markTestIncomplete("viewAction test not implemented");
        
        $this->accountController->viewAction(/* parameters */);
    }

    /**
     * Tests AccountController->addAction()
     */
    public function testAddAction()
    {
        // TODO Auto-generated AccountControllerTest->testAddAction()
        $this->markTestIncomplete("addAction test not implemented");
        
        $this->accountController->addAction(/* parameters */);
    }

    /**
     * Tests AccountController->editAction()
     */
    public function testEditAction()
    {
        // TODO Auto-generated AccountControllerTest->testEditAction()
        $this->markTestIncomplete("editAction test not implemented");
        
        $this->accountController->editAction(/* parameters */);
    }

    /**
     * Tests AccountController->getForm()
     */
    public function testGetForm()
    {
        // TODO Auto-generated AccountControllerTest->testGetForm()
        $this->markTestIncomplete("getForm test not implemented");
        
        $this->accountController->getForm(/* parameters */);
    }

    /**
     * Tests AccountController->handleSearch()
     */
    public function testHandleSearch()
    {
        // TODO Auto-generated AccountControllerTest->testHandleSearch()
        $this->markTestIncomplete("handleSearch test not implemented");
        
        $this->accountController->handleSearch(/* parameters */);
    }

    /**
     * Tests AccountController->confirmBatchDelete()
     */
    public function testConfirmBatchDelete()
    {
        // TODO Auto-generated AccountControllerTest->testConfirmBatchDelete()
        $this->markTestIncomplete("confirmBatchDelete test not implemented");
        
        $this->accountController->confirmBatchDelete(/* parameters */);
    }
}

