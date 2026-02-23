<?php
/**
 * ---------------------------------------------------------
 * Fichier : deconnexion.php
 * Projet  : NAHA Web Application
 * Rôle    : Gestion de la déconnexion utilisateur
 * ---------------------------------------------------------
 * Ce script :
 *  - Démarre la session active
 *  - Supprime toutes les variables de session
 *  - Détruit complètement la session
 *  - Redirige vers la page de connexion
 * ---------------------------------------------------------
 */

// Vérifier si une session existe déjà
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Suppression des données stockées en session
 */
$_SESSION = [];

/**
 * Suppression du cookie de session (sécurité supplémentaire)
 */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/**
 * Destruction complète de la session
 */
session_destroy();

/**
 * Redirection vers la page de connexion
 */
header("Location: ../seconnecter.php");
exit;