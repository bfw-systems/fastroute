<?php
/**
 * Fichier de configuration du module bfw-fastroute
 * Liste les routes disponibles
 * 
 * @author Vermeulen Maxime <bulton.fr@gmail.com>
 * @package bfw-controller
 * @version 1.0
 */

$bfwFastRouteConfig = new \stdClass;

/**
 * Fonction de callback appelé lorsque la route est détecté par fastroute
 * Permet de personnaliser la réaction suivant sa configuration des routes
 * 
 * @param \stdClass $returnObj : Contient les attributs à remplir pour le système de controller
 * @param array     $handler   : La valeur correspondant à la route trouvé.
 *                                  La valeur provient du tableau définie dans /configs/bfw-fastroutes/routes.php
 * 
 * @return void
 */
$bfwFastRouteConfig->routeCallback = function(&$returnObj, &$handler)
{
    \BFWFastRoute\Routing::routeCallback($returnObj, $handler); //Système par défaut.
};

return $bfwFastRouteConfig;
