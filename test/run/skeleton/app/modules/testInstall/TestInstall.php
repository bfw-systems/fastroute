<?php

namespace Modules\TestInstall;

/**
 * Controller system class
 */
class TestInstall implements \SplObserver
{
    public function update(\SplSubject $subject)
    {
        if ($subject->getAction() === 'bfw_ctrlRouterLink_subject_added') {
            $app = \BFW\Application::getInstance();
            $app->getSubjectList()
                ->getSubjectForName('ctrlRouterLink')
                ->attach($this)
            ;
        } elseif ($subject->getAction() === 'ctrlRouterLink_exec_execRoute') {
            $this->run($subject);
        }
    }
    
    public function run($subject)
    {
        $ctrlRouterInfos = $subject->getContext();

        echo '['.http_response_code().'] Target: '.$ctrlRouterInfos->target."\n";

        global $_GET;
        echo 'count get array: '.count($_GET)."\n";
        foreach($_GET as $key => $value) {
            echo '['.$key.'] => '.$value."\n";
        }
    }
}
