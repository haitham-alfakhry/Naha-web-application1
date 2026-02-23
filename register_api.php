<?php
/**
 * ============================================================
 * NAHA - API Register
 * Fichier : register_api.php
 * Description :
 *  - Gère l'inscription utilisateur via requête AJAX
 *  - Validation complète des champs (nom, prénom, email, etc.)
 *  - Vérifie l'unicité de l'email
 *  - Hash sécurisé du mot de passe
 *  - Retourne une réponse JSON structurée
 *
 * Author : HAITHAM ALFAKHRY
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "bdd.php";
$bdd = getBD();

header("Content-Type: application/json; charset=utf-8");

/**
 * Fonction de réponse JSON standardisée
 */
function respond($success, $msg, $redirect = "") {
    echo json_encode([
        "success"  => $success,
        "msg"      => $msg,
        "redirect" => $redirect
    ]);
    exit;
}

/* ===========================
   Récupération des données
   =========================== */

$nom     = trim($_POST["nom"] ?? "");
$prenom  = trim($_POST["prenom"] ?? "");
$adresse = trim($_POST["adresse"] ?? "");
$numero  = trim($_POST["numero"] ?? "");
$mail    = trim($_POST["mail"] ?? "");
$mdp1    = trim($_POST["mdp1"] ?? "");
$mdp2    = trim($_POST["mdp2"] ?? "");

/* ===========================
   Validations
   =========================== */

// Nom / prénom → lettres uniquement
if (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]{2,30}$/", $nom)) {
    respond(false, "Nom invalide.");
}

if (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]{2,30}$/", $prenom)) {
    respond(false, "Prénom invalide.");
}

// Téléphone FR (optionnel)
if (!empty($numero) && !preg_match("/^0[0-9]{9}$/", $numero)) {
    respond(false, "Numéro invalide.");
}

// Champs obligatoires
if (!$nom || !$prenom || !$mail || !$mdp1) {
    respond(false, "Merci de remplir les champs obligatoires.");
}

// Email valide
if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    respond(false, "Email invalide.");
}

// Correspondance mots de passe
if ($mdp1 !== $mdp2) {
    respond(false, "Les mots de passe ne correspondent pas.");
}

// Longueur minimale mot de passe
if (strlen($mdp1) < 6) {
    respond(false, "Le mot de passe doit contenir au moins 6 caractères.");
}

/* ===========================
   Vérifier unicité email
   =========================== */

$sql = "SELECT id_utilisateur FROM utilisateurs WHERE mail = ?";
$stmt = $bdd->prepare($sql);
$stmt->execute([$mail]);

if ($stmt->fetch()) {
    respond(false, "Un compte existe déjà avec cet e-mail.");
}

/* ===========================
   Insertion utilisateur
   =========================== */

// Hash sécurisé du mot de passe
$hash = password_hash($mdp1, PASSWORD_DEFAULT);

$sql = "INSERT INTO utilisateurs 
        (nom, prenom, adresse, numero, mail, pswrd)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $bdd->prepare($sql);
$stmt->execute([
    $nom,
    $prenom,
    $adresse,
    $numero,
    $mail,
    $hash
]);

/* ===========================
   Réponse succès
   =========================== */

respond(true, "Inscription réussie !", "seconnecter.php");