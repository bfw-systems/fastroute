<?php
/**
 * Fichier de configuration du module bfw-fastroute
 * Liste les routes disponibles
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-controller
 * @version 1.0
 */

/**
 * Exemple en utilisant le système par défaut : 
 * 
 * Controller procédural : 
 * return array(
 *      '' => array(
 *          'file' => 'index.php'
 *      ),
 *      'login' => array(
 *          'file' => 'login.php',
 *          'httpMethod' => ['GET', 'POST']
 *      ),
 *      'article-{id:\d+}' => array(
 *          'file' => 'article.php'
 *      )
 * );
 * 
 * Controller Class : 
 * return array(
 *      '' => array(
 *          'class' => 'Index',
 *          'method' => 'index'
 *      ),
 *      'login' => array(
 *          'class' => 'User',
 *          'method' => 'login',
 *          'httpMethod' => ['GET', 'POST']
 *      ),
 *      'article-{id:\d+}' => array(
 *          'class' => 'Article',
 *          'method' => 'Read'
 *      )
 * );
 */

return array();