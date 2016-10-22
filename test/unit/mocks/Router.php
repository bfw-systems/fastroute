<?php

namespace BfwFastRoute\test\unit\mocks;

class Router extends \BfwFastRoute\Router
{
    public function getModule()
    {
        return $this->module;
    }
    
    public function getConfig()
    {
        return $this->config;
    }
    
    public function getRouterLinker()
    {
        return $this->routerLinker;
    }
    
    public function getDispatcher()
    {
        return $this->dispatcher;
    }
    
    public function setDispatcher($newDispatcher)
    {
        $this->dispatcher = $newDispatcher;
    }
}
