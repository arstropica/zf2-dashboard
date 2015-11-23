<?php
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class MockUser extends AbstractHttpControllerTestCase
{

    public function mockUser($serviceManager)
    {
        // set App
        /* @var $application \Zend\Mvc\Application */
        $application = $serviceManager->get('Application');
        // Creating mock
        $mockBjy = $this->getMock('BjyAuthorize\Service\Authorize', array(
            "isAllowed"
        ), array(
            $application->getConfig(),
            $serviceManager
        ));
        
        // Bypass auth, force true
        $mockBjy->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue(true));
        
        // Overriding BjyAuthorize\Service\Authorize service
        $serviceManager->setAllowOverride(true)->setService('BjyAuthorize\Service\Authorize', $mockBjy);
    }
}
