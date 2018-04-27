<?php
/**
 * Class for the router system
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 2.0
 */

namespace BfwFastRoute;

use \Exception;
use \FastRoute;

/**
 * Permet de gérer la vue et de savoir vers quel page envoyer
 * @package bfw-fastroute
 */
class Router implements \SplObserver
{
    /**
     * @var \BFW\Module $module The bfw module instance for this module
     */
    protected $module;
    
    /**
     * @var \BFW\Config $config The bfw config instance for this module
     */
    protected $config;
    
    /**
     * @var \stdClass|null $ctrlRouterInfos The context object passed to
     * subject for the action "searchRoute".
     */
    protected $ctrlRouterInfos;
    
    /**
     * @var \FastRoute\Dispatcher $dispatcher FastRoute dispatcher
     */
    protected $dispatcher;
    
    /**
     * Constructor
     * Get config and linker instance
     * Call fastRoute dispatcher
     * 
     * @param \BFW\Module $module
     */
    public function __construct(\BFW\Module $module)
    {
        $this->module = $module;
        $this->config = $module->getConfig();
        
        $this->dispatcher = FastRoute\simpleDispatcher([
            $this,
            'addRoutesToCollector'
        ]);
    }
    
    /**
     * Observer update method
     * Call obtainCurrentRoute method on action "apprun_loadAllAppModules".
     * 
     * @param \SplSubject $subject
     * 
     * @return void
     */
    public function update(\SplSubject $subject)
    {
        if ($subject->getAction() === 'apprun_loadAllAppModules') {
            $this->obtainCurrentRoute();
        }
    }
    
    /**
     * Call by dispatcher; Add route in config to fastRoute router
     * 
     * @param FastRoute\RouteCollector $router FastRoute router
     * 
     * @return void
     */
    public function addRoutesToCollector(FastRoute\RouteCollector $router)
    {
        $routes = $this->config->getValue('routes');
        
        foreach ($routes as $slug => $infos) {
            $slug = trim($slug);

            //Défault method
            $method = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'];
            
            //If method is declared for the route
            if (isset($infos['httpMethod'])) {
                //Get the method ans remove it from httpMethod array
                $method = $infos['httpMethod'];
                unset($infos['httpMethod']);
            }

            $router->addRoute($method, $slug, $infos);
        }
    }
    
    /**
     * Obtain informations about the current route from fastRoute dispatcher
     * 
     * @return void
     */
    public function obtainCurrentRoute()
    {
        //Get current request informations
        $bfwRequest = \BFW\Request::getInstance();
        $request    = $bfwRequest->getRequest()->path;
        $method     = $bfwRequest->getMethod();
        
        //If request is index
        if ($request === '') {
            $request = '/';
        }

        //Get route information from dispatcher
        $routeInfo   = $this->dispatcher->dispatch($method, $request);
        $routeStatus = $routeInfo[0];
        
        //Get and send request http status to the controller/router linker
        $httpStatus = $this->checkStatus($routeStatus);
        
        http_response_code($httpStatus);
        $this->addInfosToCtrlRouter();
        
        if ($httpStatus !== 200) {
            return;
        }

        //Obtains datas for route from config file and send to linker
        $this->sendInfosForRouteToLinker($routeInfo[1]);
        
        //Add gets datas in route to $_GET var
        $this->addDatasToGetAndPostVar($routeInfo[1]);
        $this->addToSuperglobalVar('GET', $routeInfo[2]);
        
        $this->sendNotifyRouteFindToOthers();
    }
    
    /**
     * Get http status for response from dispatcher
     * 
     * @param int $routeStatus : Route status send by dispatcher for request
     * 
     * @return int
     */
    protected function checkStatus($routeStatus)
    {
        $httpStatus = 200;
        
        if ($routeStatus === FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            $httpStatus = 405;
        } elseif ($routeStatus === FastRoute\Dispatcher::NOT_FOUND) {
            $httpStatus = 404;
        }
        
        return $httpStatus;
    }
    
    /**
     * Update ctrlRouterInfos properties isFound and forWho
     * 
     * @return void
     */
    protected function addInfosToCtrlRouter()
    {
        $app    = \BFW\Application::getInstance();
        $forWho = $app->getConfig()->getValue('modules')['controller']['name'];
        
        $this->ctrlRouterInfos->isFound = true;
        $this->ctrlRouterInfos->forWho  = $forWho;
    }
    
    /**
     * Obtains route informations from config file and send this informations
     * to the controller/router linker
     * 
     * @param array $routeInfos : Route information from config file
     * 
     * @return void
     * 
     * @throws \Exception If target not define in config file
     */
    protected function sendInfosForRouteToLinker(array $routeInfos)
    {
        if (!isset($routeInfos['target'])) {
            throw new Exception('Router : target not defined');
        }
        
        $this->ctrlRouterInfos->target = $routeInfos['target'];
    }
    
    /**
     * Add datas into a superglobal var
     * 
     * @param string $globalVarName : Name of the superglobal var
     * @param array $datasToAdd : Datas to add to $_GET var
     * 
     * @return void
     */
    protected function addToSuperglobalVar($globalVarName, array $datasToAdd)
    {
        global ${'_'.$globalVarName};
        ${'_'.$globalVarName} = array_merge(${'_'.$globalVarName}, $datasToAdd);
    }
    
    /**
     * If property 'get' has been declared into current route config, add
     * them into superglobal $_GET .
     * 
     * @param array $routeInfos route informations declared in config
     * 
     * @return void
     */
    protected function addDatasToGetAndPostVar(array $routeInfos)
    {
        if (isset($routeInfos['get'])) {
            $this->addToSuperglobalVar('GET', $routeInfos['get']);
        }
    }
    
    /**
     * Send to all observer of Application a notify who contains the message
     * "request_route_find" to say the route for the current request has been
     * found by us.
     * 
     * @return void
     */
    protected function sendNotifyRouteFindToOthers()
    {
        $app = \BFW\Application::getInstance();
        $app->addNotification('request_route_find');
    }
}
