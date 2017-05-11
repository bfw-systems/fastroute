<?php

$config = $this->getConfig();
$linker = \BFW\ControllerRouterLink::getInstance();

echo '['.http_response_code().'] Target: '.$linker->getTarget()."\n";

global $_GET;
echo 'count get array: '.count($_GET)."\n";
foreach($_GET as $key => $value) {
    echo '['.$key.'] => '.$value."\n";
}
