<?php
/**
 * ---------------------------------------------------------
 * Fichier : login_api.php
 * Projet  : NAHA Web Application
 * Rôle    : Traitement de l'authentification utilisateur
 * ---------------------------------------------------------
 * Ce script :
 *  - Vérifie que la requête est en POST
 *  - Nettoie les données reçues
 *  - Vérifie l'existence de l'utilisateur
 *  - Valide le mot de passe
 *  - Initialise la session sécurisée
 * ---------------------------------------------------------
 */

// Démarrage de la session
session_start();

// Inclusion de la connexion PDO
require_once '../includes/bdd.php';
$bdd = getBD();

/**
 * Sécurisation : autoriser uniquement les requêtes POST
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../seconnecter.php?err=" . urlencode("Accès non autorisé."));
    exit;
}

/**
 * Nettoyage et récupération des données
 */
$mail = filter_input(INPUT_POST, 'mail', FILTER_SANITIZE_EMAIL);
$mdp  = $_POST['mdp'] ?? '';

if (empty($mail) || empty($mdp)) {
    header("Location: ../seconnecter.php?err=" . urlencode("Veuillez remplir tous les champs."));
    exit;
}

/**
 * Recherche de l'utilisateur dans la base
 */
$sql = "SELECT id_utilisateur, nom, prenom, mail, pswrd 
        FROM utilisateurs 
        WHERE mail = :mail 
        LIMIT 1";

$stmt = $bdd->prepare($sql);
$stmt->execute(['mail' => $mail]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

/**
 * Vérification de l'existence du compte
 */
if (!$user) {
    header("Location: ../seconnecter.php?err=" . urlencode("Adresse e-mail inconnue."));
    exit;
}

/**
 * Vérification du mot de passe (hash sécurisé)
 */
if (!password_verify($mdp, $user['pswrd'])) {
    header("Location: ../seconnecter.php?err=" . urlencode("Mot de passe incorrect."));
    exit;
}

/**
 * Protection contre le session fixation
 */
session_regenerate_id(true);

/**
 * Stockage des informations essentielles en session
 */
$_SESSION['utilisateur'] = [
    'id_utilisateur' => $user['id_utilisateur'],
    'nom'            => $user['nom'],
    'prenom'         => $user['prenom'],
    'mail'           => $user['mail']
];

/**
 * Redirection vers la page d'accueil après connexion réussie
 */
header("Location: ../accueil.php");
exit;