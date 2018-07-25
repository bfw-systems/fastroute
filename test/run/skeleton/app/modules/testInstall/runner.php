<?php

$observer = new \Modules\TestInstall\TestInstall;

$app        = \BFW\Application::getInstance();
$appSubject = $app->getSubjectList()->getSubjectForName('ApplicationTasks');
$appSubject->attach($observer);
