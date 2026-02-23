<?php
/**
 * ============================================================
 * NAHA - Update Email
 * Author : HAITHAM ALFAKHRY
 * File : update_email.php
 * Description :
 *  Secure email update with validation,
 *  duplication check and PDO error handling.
 * ============================================================
 */

declare(strict_types=1);

session_start();
require_once __DIR__ . "/bdd.php";

// ============================================================
// Vérification session
// ============================================================
if (!isset($_SESSION['utilisateur']['id'])) {
    header("Location: seconnecter.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: parametres.php");
    exit;
}

$id = (int) $_SESSION['utilisateur']['id'];
$new_email = trim($_POST['new_email'] ?? '');

if ($new_email === '') {
    header("Location: parametres.php?error=Email vide");
    exit;
}

// ============================================================
// Validation format email
// ============================================================
if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    header("Location: parametres.php?error=Email invalide");
    exit;
}

try {

    $bdd = getBD();

    // Vérifier email actuel
    $stmtCurrent = $bdd->prepare("SELECT mail FROM utilisateurs WHERE id_utilisateur = ?");
    $stmtCurrent->execute([$id]);
    $currentUser = $stmtCurrent->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        throw new Exception("Utilisateur introuvable.");
    }

    if ($currentUser['mail'] === $new_email) {
        header("Location: parametres.php?error=Email identique");
        exit;
    }

    // Vérifier si email déjà utilisé
    $check = $bdd->prepare("SELECT id_utilisateur FROM utilisateurs WHERE mail = ?");
    $check->execute([$new_email]);

    if ($check->fetch()) {
        header("Location: parametres.php?error=Email deja utilise");
        exit;
    }

    // Update email
    $update = $bdd->prepare("UPDATE utilisateurs SET mail = ? WHERE id_utilisateur = ?");
    $update->execute([$new_email, $id]);

    header("Location: parametres.php?success=email");
    exit;

} catch (Exception $e) {

    error_log("Erreur update_email : " . $e->getMessage());

    header("Location: parametres.php?error=Erreur serveur");
    exit;
}