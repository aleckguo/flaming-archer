<?php

namespace Fa\Middleware;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-11-14 at 06:50:48.
 */
class AuthenticationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Zend\Authentication\AuthenticationService
     */
    private $authenticationService;

    /**
     * @var Authentication
     */
    private $middleware;

    protected function setUp()
    {
        parent::setUp();
        $config = array(
            'login.url' => '/login',
            'secured.urls' => array(
                array('path' => '/admin'),
                array('path' => '/admin/.+')
            )
        );
        $this->auththenticationService = $this->getMock('Zend\Authentication\AuthenticationService');
        $this->middleware = new Authentication($this->auththenticationService, $config);
    }

    protected function tearDown()
    {
        $this->middleware = null;
        parent::tearDown();
    }

    public function testVisitHomePageNotLoggedInSucceeds()
    {
        \Slim\Environment::mock(array(
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/'
        ));

        $app = new \Slim\Slim();

        $app->get('/', function() {
            echo 'Success';
        });

        $this->middleware->setApplication($app);
        $this->middleware->setNextMiddleware($app);
        $this->middleware->call();
        $response = $app->response();
        $this->assertTrue($response->isOk());
    }

    public function testVisitAdminPageNotLoggedInRedirectsToLogin()
    {
        \Slim\Environment::mock(array(
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/admin'
        ));

        $app = new \Slim\Slim();

        $app->get('/admin', function() {
            echo 'Y U NO LOGGED IN';
        });

        $this->auththenticationService->expects($this->once())
            ->method('hasIdentity')
            ->will($this->returnValue(false));

        $this->middleware->setApplication($app);
        $this->middleware->setNextMiddleware($app);
        $this->middleware->call();
        $response = $app->response();
        $this->assertTrue($response->isRedirect());
        $this->assertEquals(302, $response->status());
        $this->assertEquals('/login', $response->header('location'));
    }

    public function testVisitAdminPageLoggedInSucceeds()
    {
        \Slim\Environment::mock(array(
            'SCRIPT_NAME' => '/index.php',
            'PATH_INFO' => '/admin'
        ));

        $app = new \Slim\Slim();

        $app->get('/admin', function() {
            echo 'Success';
        });

        $this->auththenticationService->expects($this->once())
            ->method('hasIdentity')
            ->will($this->returnValue(true));

        $this->middleware->setApplication($app);
        $this->middleware->setNextMiddleware($app);
        $this->middleware->call();
        $response = $app->response();
        $this->assertTrue($response->isOk());
    }

}