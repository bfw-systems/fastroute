<?php

$this->router = new \BfwFastRoute\Router($this);

$app = \BFW\Application::getInstance();
$app->attach($this->router);
