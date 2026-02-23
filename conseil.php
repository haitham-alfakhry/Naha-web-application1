<?php
/**
 * ============================================================
 * NAHA - Conseil du jour & Newsletter AJAX
 * Fichier : conseil.php
 * Description :
 *  - Affiche un conseil du jour (rotation annuelle)
 *  - Permet l'inscription newsletter en AJAX
 *  - Protection CSRF
 *  - Sécurisation des entrées utilisateur
 *
 * Sécurité :
 *  - PDO préparé
 *  - Token CSRF
 *  - htmlspecialchars sur affichage
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
 * 1️⃣ CONSEIL DU JOUR
 * ============================================================ */

$dayIndex = date('z'); // Jour de l'année (0 → 365)
$conseil  = null;

try {
    // Nombre total de conseils
    $total = (int)$bdd->query("SELECT COUNT(*) FROM conseils")->fetchColumn();

    if ($total > 0) {

        // Rotation automatique des conseils
        $offset = $dayIndex % $total;

        $stmt = $bdd->prepare("SELECT texte FROM conseils LIMIT 1 OFFSET :o");
        $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $conseil = $stmt->fetch(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    $conseil = null;
}

/* Fallback si aucun conseil trouvé */
if (!$conseil) {
    $conseil = [
        "texte" => "Aucun conseil disponible aujourd’hui."
    ];
}


/* ============================================================
 * 2️⃣ GÉNÉRATION TOKEN CSRF
 * ============================================================ */

if (empty($_SESSION['csrf_news'])) {
    $_SESSION['csrf_news'] = bin2hex(random_bytes(32));
}


/* ============================================================
 * 3️⃣ INSCRIPTION NEWSLETTER (AJAX)
 * ============================================================ */

if (isset($_GET['ajax']) && $_GET['ajax'] === 'newsletter') {

    header("Content-Type: application/json; charset=utf-8");

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode(['ok' => false, 'msg' => 'Méthode non autorisée.']);
        exit;
    }

    $token = $_POST['csrf'] ?? "";

    if (!hash_equals($_SESSION['csrf_news'], $token)) {
        echo json_encode(['ok' => false, 'msg' => 'Token CSRF invalide. Recharge la page.']);
        exit;
    }

    $email = trim($_POST["email"] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok' => false, 'msg' => 'Adresse email invalide.']);
        exit;
    }

    try {

        $stmt = $bdd->prepare("
            INSERT IGNORE INTO newsletter_users(email) 
            VALUES (:email)
        ");

        $stmt->execute(['email' => $email]);

        echo json_encode([
            'ok'  => true,
            'msg' => 'Inscription réussie ! Tu recevras nos conseils 🌱'
        ]);

    } catch (Exception $e) {

        echo json_encode([
            'ok'  => false,
            'msg' => 'Erreur serveur. Réessaie plus tard.'
        ]);
    }

    exit;
}
?>

<!-- ============================================================
     4️⃣ AFFICHAGE HTML
============================================================ -->

<link rel="stylesheet" href="conseil.css">

<div class="conseil-card">
    <h3>Conseil du jour 🍃</h3>
    <p><?= htmlspecialchars($conseil["texte"], ENT_QUOTES, 'UTF-8') ?></p>
</div>

<script src="conseil.js" defer></script>