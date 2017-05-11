<?php

namespace BfwFastRoute\test\unit;

use \atoum;
use \BFW\test\helpers\ApplicationInit as AppInit;

require_once(__DIR__.'/../../../vendor/autoload.php');
require_once(__DIR__.'/../../../vendor/bulton-fr/bfw/test/unit/helpers/ApplicationInit.php');
require_once(__DIR__.'/../../../vendor/bulton-fr/bfw/test/unit/mocks/src/class/Config.php');
require_once(__DIR__.'/../../../vendor/bulton-fr/bfw/test/unit/mocks/src/class/Module.php');

class Router extends atoum
{
    /**
     * @var $class : Instance de la class
     */
    protected $class;
    
    protected $module;
    
    /**
     * Instanciation de la class avant chaque méthode de test
     */
    public function beforeTestMethod($testMethod)
    {
        AppInit::init([
            'vendorDir' => __DIR__.'/../../../vendor'
        ]);
        
        $config = new \BFW\test\unit\mocks\Config('unit_test');
        $config->forceConfig(
            'routes',
            (object) [
                'routes' =>  [
                    '/' => [
                        'target' => 'index.php'
                    ],
                    '/login' => [
                        'target' => 'login.php',
                        'httpMethod' => ['GET', 'POST']
                    ],
                    '/no-target' => [
                        'httpMethod' => ['GET', 'POST']
                    ],
                    '/article-{id:\d+}' => [
                        'target' => 'article.php',
                        'get' => ['action' => 'read']
                    ]
                ]
            ]
        );
        
        $this->module = new \BFW\test\unit\mocks\Module('unit_test', false);
        $this->module->setConfig($config);
        
        if ($testMethod === 'testConstruct') {
            return;
        }
        
        $this->class = new \BfwFastRoute\test\unit\mocks\Router($this->module);
    }
    
    public function testConstruct()
    {
        $this->assert('test Router::__construct')
            ->if($this->class = new \BfwFastRoute\test\unit\mocks\Router($this->module))
            ->then
            ->object($this->class->getModule())
                ->isIdenticalTo($this->module)
            ->object($this->class->getConfig())
            ->object($this->class->getRouterLinker())
                ->isIdenticalTo(\BFW\ControllerRouterLink::getInstance())
            ->object($this->class->getDispatcher())
                ->isInstanceOf('FastRoute\Dispatcher');
    }
    
    public function testAddRoutesToCollector()
    {
        $staticOnlyIndex = [
            '/' => [
                'target' => 'index.php'
            ]
        ];
        
        $staticIndexLoginAndNoTarget = [
            '/' => [
                'target' => 'index.php'
            ],
            '/login' => [
                'target' => 'login.php'
            ],
            '/no-target' => []
        ];
        
        $variableArticle = [
            0 => [
                'regex' => '~^(?|/article\-(\d+))$~',
                'routeMap' => [
                    2 => [
                        0 => [
                            'target' => 'article.php',
                            'get' => [
                                'action' => 'read'
                            ]
                        ],
                        1 => [
                            'id' => 'id'
                        ]
                    ]
                ]
            ]
        ];
        
        $this->assert('test Router::addRoutesToCollector')
            ->if($this->class->setDispatcher(
                \FastRoute\simpleDispatcher(
                    [$this->class, 'addRoutesToCollector'],
                    ['dispatcher' => '\\BfwFastRoute\\test\\unit\\mocks\\Dispatcher']
                )
            ))
            ->given($dispatcher = $this->class->getDispatcher())
            ->given($staticRouteMap = $dispatcher->staticRouteMap)
            ->given($variableRouteData = $dispatcher->variableRouteData)
            
            ->array($staticRouteMap['GET'])
                ->isEqualTo($staticIndexLoginAndNoTarget)
            ->array($staticRouteMap['HEAD'])
                ->isEqualTo($staticOnlyIndex)
            ->array($staticRouteMap['POST'])
                ->isEqualTo($staticIndexLoginAndNoTarget)
            ->array($staticRouteMap['PUT'])
                ->isEqualTo($staticOnlyIndex)
            ->array($staticRouteMap['PATCH'])
                ->isEqualTo($staticOnlyIndex)
            ->array($staticRouteMap['DELETE'])
                ->isEqualTo($staticOnlyIndex)
            
            ->array($variableRouteData['GET'])
                ->isEqualTo($variableArticle)
            ->array($variableRouteData['HEAD'])
                ->isEqualTo($variableArticle)
            ->array($variableRouteData['POST'])
                ->isEqualTo($variableArticle)
            ->array($variableRouteData['PUT'])
                ->isEqualTo($variableArticle)
            ->array($variableRouteData['PATCH'])
                ->isEqualTo($variableArticle)
            ->array($variableRouteData['DELETE'])
                ->isEqualTo($variableArticle);
    }
    
    public function testObtainCurrentRouteForIndex()
    {
        $this->assert('test Router::obtainCurrentRoute for index')
            ->if($_SERVER['REQUEST_METHOD'] = 'GET')
            ->and($_SERVER['REQUEST_URI'] = 'https://www.bulton.fr')
            ->and(\BFW\Application::getInstance()->getRequest()->runDetect())
            ->then
            ->given($linker = \BFW\ControllerRouterLink::getInstance())
            ->if($this->class->obtainCurrentRoute())
            ->then
            ->string($linker->getTarget())
                ->isEqualTo('index.php');
    }
    
    public function testObtainCurrentRouteForLoginWithGetMethod()
    {
        $this->assert('test Router::obtainCurrentRoute for login on GET')
            ->if($_SERVER['REQUEST_METHOD'] = 'GET')
            ->and($_SERVER['REQUEST_URI'] = 'https://www.bulton.fr/login')
            ->and(\BFW\Application::getInstance()->getRequest()->runDetect())
            ->then
            ->given($linker = \BFW\ControllerRouterLink::getInstance())
            ->if($this->class->obtainCurrentRoute())
            ->then
            ->string($linker->getTarget())
                ->isEqualTo('login.php');
    }
    
    public function testObtainCurrentRouteForLoginWithPostMethod()
    {
        $this->assert('test Router::obtainCurrentRoute for login on POST')
            ->if($_SERVER['REQUEST_METHOD'] = 'POST')
            ->and($_SERVER['REQUEST_URI'] = 'https://www.bulton.fr/login')
            ->and(\BFW\Application::getInstance()->getRequest()->runDetect())
            ->then
            ->given($linker = \BFW\ControllerRouterLink::getInstance())
            ->if($this->class->obtainCurrentRoute())
            ->then
            ->string($linker->getTarget())
                ->isEqualTo('login.php');
    }
    
    public function testObtainCurrentRouteForLoginWithPutMethod()
    {
        $this->assert('test Router::obtainCurrentRoute for login on PUT')
            ->if($_SERVER['REQUEST_METHOD'] = 'PUT')
            ->and($_SERVER['REQUEST_URI'] = 'https://www.bulton.fr/login')
            ->and(\BFW\Application::getInstance()->getRequest()->runDetect())
            ->then
            ->given($linker = \BFW\ControllerRouterLink::getInstance())
            ->if($this->class->obtainCurrentRoute())
            ->then
            ->variable($linker->getTarget())
                ->isNull()
            ->integer(http_response_code())
                ->isEqualTo(405);
    }
    
    public function testObtainCurrentRouteForArticle()
    {
        global $_GET;
        
        $this->assert('test Router::obtainCurrentRoute for article')
            ->if($_SERVER['REQUEST_METHOD'] = 'GET')
            ->and($_SERVER['REQUEST_URI'] = 'https://www.bulton.fr/article-10')
            ->and(\BFW\Application::getInstance()->getRequest()->runDetect())
            ->then
            ->given($linker = \BFW\ControllerRouterLink::getInstance())
            ->if($this->class->obtainCurrentRoute())
            ->then
            ->string($linker->getTarget())
                ->isEqualTo('article.php')
            ->array($_GET)
                ->isEqualTo([
                    'id'     => 10,
                    'action' => 'read'
                ]);
    }
    
    public function testObtainCurrentRouteForNoTarget()
    {
        $this->assert('test Router::obtainCurrentRoute for no-target')
            ->if($_SERVER['REQUEST_METHOD'] = 'GET')
            ->and($_SERVER['REQUEST_URI'] = 'https://www.bulton.fr/no-target')
            ->and(\BFW\Application::getInstance()->getRequest()->runDetect())
            ->then
            ->given($linker = \BFW\ControllerRouterLink::getInstance())
            ->given($class = $this->class)
            ->exception(function() use ($class) {
                $class->obtainCurrentRoute();
            })
                ->hasMessage('Router : target not defined');
    }
    
    public function testObtainCurrentRouteForUnknownRoute()
    {
        $this->assert('test Router::obtainCurrentRoute for unknown route')
            ->if($_SERVER['REQUEST_METHOD'] = 'GET')
            ->and($_SERVER['REQUEST_URI'] = 'https://www.bulton.fr/unknown-route')
            ->and(\BFW\Application::getInstance()->getRequest()->runDetect())
            ->then
            ->given($linker = \BFW\ControllerRouterLink::getInstance())
            ->if($this->class->obtainCurrentRoute())
            ->then
            ->variable($linker->getTarget())
                ->isNull()
            ->integer(http_response_code())
                ->isEqualTo(404);
    }
}
