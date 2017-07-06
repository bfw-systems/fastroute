<?php

namespace Modules\TestInstall;

/**
 * Controller system class
 */
class TestInstall implements \SplObserver
{
    public function update(\SplSubject $subject)
    {
        if ($subject->getAction() === 'bfw_run_finish') {
            $this->run();
        }
    }
    
    public function run()
    {
        $linker = \BFW\ControllerRouterLink::getInstance();

        echo '['.http_response_code().'] Target: '.$linker->getTarget()."\n";

        global $_GET;
        echo 'count get array: '.count($_GET)."\n";
        foreach($_GET as $key => $value) {
            echo '['.$key.'] => '.$value."\n";
        }
    }
}
