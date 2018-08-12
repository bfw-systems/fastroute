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
     * @const ERR_TARGET_NOT_DECLARED : Error code if the target has not
     * been declared
     */
    const ERR_TARGET_NOT_DECLARED = 2001001;
    
    /**
     * @var \BFW\Module $module The bfw module instance for this module
     */
    protected $module;
    
    /**
     * @var \BFW\Config $config The bfw config instance for this module
     */
    protected $config;
    
    /**
     * @var object|null $ctrlRouterInfos The context object passed to
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
     * Getter accessor for module property
     * 
     * @return \BFW\Module
     */
    public function getModule(): \BFW\Module
    {
        return $this->module;
    }

    /**
     * Getter accessor for config property
     * 
     * @return \BFW\Config
     */
    public function getConfig(): \BFW\Config
    {
        return $this->config;
    }

    /**
     * Getter accessor for ctrlRouterInfos property
     * 
     * @return object
     */
    public function getCtrlRouterInfos()
    {
        return $this->ctrlRouterInfos;
    }

    /**
     * Getter accessor for dispatcher property
     * 
     * @return \FastRoute\Dispatcher
     */
    public function getDispatcher(): \FastRoute\Dispatcher
    {
        return $this->dispatcher;
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
        if ($subject->getAction() === 'ctrlRouterLink_exec_searchRoute') {
            $this->obtainCtrlRouterInfos($subject);
            
            if ($this->ctrlRouterInfos->isFound === false) {
                $this->searchRoute();
            }
        }
    }
    
    /**
     * Set the property ctrlRouterInfos with the context object obtain linked
     * to the subject.
     * Allow override to get only some part. And used for unit test.
     * 
     * @param \BFW\Subject $subject
     * 
     * @return void
     */
    protected function obtainCtrlRouterInfos(\BFW\Subject $subject)
    {
        $this->ctrlRouterInfos = $subject->getContext();
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
        $this->module->monolog->getLogger()->debug('Add all routes.');
        
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
    protected function searchRoute()
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
        
        $this->module
            ->monolog
            ->getLogger()
            ->debug(
                'Search the current route into declared routes.',
                [
                    'request' => $request,
                    'method' => $method,
                    'status' => $routeStatus
                ]
            );
        
        //Get and send request http status to the controller/router linker
        $httpStatus = $this->checkStatus($routeStatus);
        
        if ($httpStatus === 404) {
            //404 will be declared by \BFW\Application::runCtrlRouterLink()
            return;
        }
        
        http_response_code($httpStatus);
        $this->addInfosToCtrlRouter();
        
        if ($httpStatus !== 200) {
            return;
        }
        
        //Obtains datas for route from config file and send to linker
        $this->addTargetToCtrlRouter($routeInfo[1]);

        //Add gets datas in route to $_GET var
        $this->addDatasToGetVar($routeInfo[1]);
        $this->addToSuperglobalVar('GET', $routeInfo[2]);
    }
    
    /**
     * Get http status for response from dispatcher
     * 
     * @param int $routeStatus : Route status send by dispatcher for request
     * 
     * @return int
     */
    protected function checkStatus(int $routeStatus): int
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
        $modulesConfig = \BFW\Application::getInstance()
            ->getConfig()
            ->getValue('modules', 'modules.php')
        ;
        $forWho        = $modulesConfig['controller']['name'];
        
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
    protected function addTargetToCtrlRouter(array $routeInfos)
    {
        if (array_key_exists('target', $routeInfos) === false) {
            throw new Exception(
                'Router : target not defined',
                self::ERR_TARGET_NOT_DECLARED
            );
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
    protected function addToSuperglobalVar(
        string $globalVarName,
        array $datasToAdd
    ) {
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
    protected function addDatasToGetVar(array $routeInfos)
    {
        if (isset($routeInfos['get'])) {
            $this->addToSuperglobalVar('GET', $routeInfos['get']);
        }
    }
}
