<?php

$observer = new \Modules\displayRoute\Observer;

$app        = \BFW\Application::getInstance();
$appSubject = $app->getSubjectList()->getSubjectByName('ctrlRouterLink');
$appSubject->attach($observer);
