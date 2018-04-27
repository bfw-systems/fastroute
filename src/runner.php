<?php

$this->router = new \BfwFastRoute\Router($this);

$app        = \BFW\Application::getInstance();
$appSubject = $app->getSubjectList()->getSubjectForName('ApplicationTasks');
$appSubject->attach($this->router);
