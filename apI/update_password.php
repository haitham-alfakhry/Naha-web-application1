<?php
/**
 * ============================================================
 * NAHA - Update Password
 * Author : HAITHAM ALFAKHRY
 * File : update_password.php
 * Description :
 *  Secure password update with validation, hashing,
 *  session protection and PDO error handling.
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

$old     = trim($_POST['old_password'] ?? '');
$new     = trim($_POST['new_password'] ?? '');
$confirm = trim($_POST['confirm_password'] ?? '');

// ============================================================
// Validation basique
// ============================================================
if ($old === '' || $new === '' || $confirm === '') {
    header("Location: parametres.php?error=Champs vides");
    exit;
}

if ($new !== $confirm) {
    header("Location: parametres.php?error=Confirmation incorrecte");
    exit;
}

// Minimum sécurité mot de passe
if (strlen($new) < 8) {
    header("Location: parametres.php?error=Mot de passe trop court");
    exit;
}

try {

    $bdd = getBD();

    // ========================================================
    // Récupération mot de passe actuel
    // ========================================================
    $stmt = $bdd->prepare("SELECT pswrd FROM utilisateurs WHERE id_utilisateur = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Utilisateur introuvable.");
    }

    if (!password_verify($old, $user['pswrd'])) {
        header("Location: parametres.php?error=Mot de passe actuel incorrect");
        exit;
    }

    // ========================================================
    // Hash du nouveau mot de passe
    // ========================================================
    $newHash = password_hash($new, PASSWORD_DEFAULT);

    $update = $bdd->prepare("UPDATE utilisateurs SET pswrd = ? WHERE id_utilisateur = ?");
    $update->execute([$newHash, $id]);

    header("Location: parametres.php?success=password");
    exit;

} catch (Exception $e) {

    error_log("Erreur update_password : " . $e->getMessage());

    header("Location: parametres.php?error=Erreur serveur");
    exit;
}