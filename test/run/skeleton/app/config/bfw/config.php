<?php

$config = require(__DIR__.'/config.php.original');

$config['modules']['router']['name']    = 'bfw-fastroute';
$config['modules']['router']['enabled'] = true;

return $config;
