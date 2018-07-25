<?php

$this->monolog = new \BFW\Monolog(
    'bfw-fastroute',
    \BFW\Application::getInstance()->getConfig()
);
$this->monolog->addAllHandlers();

$this->router = new \BfwFastRoute\Router($this);

$app        = \BFW\Application::getInstance();
$appSubject = $app->getSubjectList()->getSubjectForName('ApplicationTasks');
$appSubject->attach($this->router);
