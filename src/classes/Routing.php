<?php

/**
 * Classes gérant le routing
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @version 1.0
 */

namespace BFWFastRoute;

use \FastRoute;

/**
 * Permet de gérer la vue et de savoir vers quel page envoyer
 * @package bfw-controller
 */
class Routing
{
    /**
     * @var $kernel L'instance du Kernel
     */
    protected $kernel;
    protected $controller;
    protected $getArgs = array();
    protected $config;

    public function __construct(&$controller)
    {
        $this->kernel     = getKernel();
        $this->controller = $controller;
        
        $this->config = require(path.'configs/bfw-fastroute/config.php');
    }

    public function detectUri()
    {
        $returnObj = new \stdClass;

        $returnObj->fileArbo    = '';
        $returnObj->nameCtr     = '';
        $returnObj->nameMethode = '';

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uriParse   = parse_url($_SERVER['REQUEST_URI']);
        $request    = rawurldecode($uriParse['path']);

        $routes = require(path.'configs/bfw-fastroute/routes.php');

        $dispatcher = FastRoute\simpleDispatcher(
        function(FastRoute\RouteCollector $router) use ($routes)
        {
            foreach($routes as $slug => $infos)
            {
                $slug = trim($slug);

                $method = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'];
                if(isset($infos['httpMethod']))
                {
                    $method = $infos['httpMethod'];
                }

                $handler = $infos;
                unset($handler['httpMethod']);

                $router->addRoute($method, $slug, $handler);
            }
        });
        
        if($request === '')
        {
            $request = '/';
        }

        $routeInfo   = $dispatcher->dispatch($httpMethod, $request);
        $routeStatus = $routeInfo[0];
        $routeError  = 0;

        if($routeStatus === FastRoute\Dispatcher::METHOD_NOT_ALLOWED)
        {
            $routeError = 405;
        }
        if($routeStatus !== FastRoute\Dispatcher::FOUND)
        {
            $routeError = 404;
        }

        if($routeError !== 0)
        {
            ErrorView($routeError, true);
            //exit doing in ErrorView
        }

        $fastRouteCallback = $this->config->routeCallback;
        if(isset($fastRouteCallback) && is_callable($fastRouteCallback))
        {
            $fastRouteCallback($returnObj, $routeInfo[1]);
        }
        
        $this->getArgs = $routeInfo[2];
        
        return $returnObj;
    }

    public function detectGet()
    {
        global $_GET;

        $_GET = array_merge($_GET, $this->getArgs);
    }

    public static function routeCallback(&$returnObj, &$handler)
    {
        if(isset($handler['file']))
        {
            $returnObj->fileArbo = $handler['file'];
        }

        if(isset($handler['class']))
        {
            $returnObj->nameCtr = $handler['class'];
        }

        if(isset($handler['method']))
        {
            $returnObj->nameMethode = $handler['method'];
        }
    }
}
