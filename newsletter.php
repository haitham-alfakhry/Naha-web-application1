<?php
/**
 * ============================================================
 * NAHA - Newsletter Subscription Handler
 * Fichier : newsletter.php
 * Description :
 *  - Gère l'inscription à la newsletter
 *  - Vérifie la validité de l’email
 *  - Insère en base (INSERT IGNORE pour éviter doublons)
 *  - Retourne message via session
 *
 * Author : HAITHAM ALFAKHRY
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "bdd.php";
$bdd = getBD();

/* ============================================================
 * 1️⃣ Vérification méthode POST
 * ============================================================ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: accueil.php');
    exit;
}

/* ============================================================
 * 2️⃣ Sécurisation et validation email
 * ============================================================ */
$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['newsletter_msg'] = "Adresse email invalide ❌";
    header("Location: accueil.php#newsletter");
    exit;
}

/* ============================================================
 * 3️⃣ Insertion en base de données
 * ============================================================ */
try {
    $stmt = $bdd->prepare("
        INSERT IGNORE INTO newsletter_users (email, date_inscription)
        VALUES (:email, NOW())
    ");
    $stmt->execute(['email' => $email]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['newsletter_msg'] = "Inscription réussie 🎉 Tu recevras bientôt nos conseils 🍃";
    } else {
        $_SESSION['newsletter_msg'] = "Tu es déjà inscrit 😉";
    }

} catch (Exception $e) {
    $_SESSION['newsletter_msg'] = "Erreur serveur, merci de réessayer plus tard 🙏";
}

/* ============================================================
 * 4️⃣ Redirection vers l’accueil
 * ============================================================ */
header("Location: accueil.php#newsletter");
exit;