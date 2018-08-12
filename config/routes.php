<?php
/**
 * Config file for fastroute module
 * Declare all routes
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-fastroute
 * @version 2.0
 */

/**
 * Exemple of config route
 * To know data to set in "target" property, please refer you
 * to the controller module.
 * 
 * Exemple if target contains php file to call
 *
 * return [
 *     'routes' => [
 *         '/' => [
 *             'target' => 'index.php'
 *         ],
 *         '/login' => [
 *             'target' => 'login.php',
 *             'httpMethod' => ['GET', 'POST']
 *         ],
 *         '/article-{id:\d+}' => [
 *             'target' => 'article.php',
 *             'get' => ['action' => 'read']
 *         ]
 *     ]
 * ];
 */

return [
    'routes' => []
];
