<?php
/**
 * ============================================================
 * NAHA - Page Contact + API AJAX
 * Fichier : contact.php
 * Description :
 *  - Affiche le formulaire de contact
 *  - Gère le traitement AJAX sécurisé
 *  - Protection CSRF
 *  - Validation des champs
 *  - Insertion en base de données
 *  - Support utilisateur connecté ou invité
 *
 * Author : HAITHAM ALFAKHRY
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'bdd.php';
$bdd = getBD();

/* ============================================================
 * 1️⃣ Génération token CSRF
 * ============================================================ */
if (empty($_SESSION['csrf_contact'])) {
    $_SESSION['csrf_contact'] = bin2hex(random_bytes(32));
}

/* ============================================================
 * 2️⃣ Endpoint AJAX : ?ajax=contact
 * ============================================================ */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'contact') {

    header('Content-Type: application/json; charset=utf-8');

    /* ---------- Vérification méthode ---------- */
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Méthode non autorisée.']);
        exit;
    }

    /* ---------- Vérification CSRF ---------- */
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf_contact'] ?? '', $token)) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Token CSRF invalide. Recharge la page.'
        ]);
        exit;
    }

    /* ============================================================
     * 3️⃣ Nettoyage des données
     * ============================================================ */
    $nom     = trim($_POST['nom'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $sujet   = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $errors = [];

    /* ---------- Validation ---------- */
    if ($nom === '' || mb_strlen($nom) < 2) {
        $errors['nom'] = 'Ton nom est trop court.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Adresse e-mail invalide.';
    }

    if ($sujet === '' || mb_strlen($sujet) < 3) {
        $errors['sujet'] = 'Sujet trop court.';
    }

    if ($message === '' || mb_strlen($message) < 10) {
        $errors['message'] = 'Ton message est trop court.';
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    /* ============================================================
     * 4️⃣ Insertion base de données
     * ============================================================ */
    try {

        $idUser = null;

        if (isset($_SESSION['utilisateur']['id_utilisateur'])) {
            $idUser = (int) $_SESSION['utilisateur']['id_utilisateur'];
        }

        if ($idUser === null) {

            $sql = "INSERT INTO contact_message 
                    (nom, email, sujet, message, created_at)
                    VALUES (:nom, :email, :sujet, :message, NOW())";

            $stmt = $bdd->prepare($sql);
            $stmt->execute([
                ':nom'     => $nom,
                ':email'   => $email,
                ':sujet'   => $sujet,
                ':message' => $message
            ]);

        } else {

            $sql = "INSERT INTO contact_message 
                    (id_utilisateur, nom, email, sujet, message, created_at)
                    VALUES (:idUser, :nom, :email, :sujet, :message, NOW())";

            $stmt = $bdd->prepare($sql);
            $stmt->execute([
                ':idUser'  => $idUser,
                ':nom'     => $nom,
                ':email'   => $email,
                ':sujet'   => $sujet,
                ':message' => $message
            ]);
        }

        echo json_encode([
            'ok' => true,
            'message' => 'Message bien envoyé, merci !'
        ]);

    } catch (Exception $e) {

        http_response_code(500);

        echo json_encode([
            'ok' => false,
            'error' => 'Erreur serveur.'
        ]);
    }

    exit;
}
?>