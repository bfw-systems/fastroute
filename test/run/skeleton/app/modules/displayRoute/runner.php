<?php

$observer = new \Modules\displayRoute\Observer;

$app        = \BFW\Application::getInstance();
$appSubject = $app->getSubjectList()->getSubjectForName('ApplicationTasks');
$appSubject->attach($observer);
