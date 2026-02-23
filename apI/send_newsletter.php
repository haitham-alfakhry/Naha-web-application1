<?php
/**
 * ============================================================
 * NAHA - Daily Newsletter Sender
 * Author : HAITHAM ALFAKHRY
 * File : send_newsletter.php
 * Description :
 *  Récupère le conseil du jour et l'envoie
 *  à tous les abonnés newsletter.
 * ============================================================
 */

declare(strict_types=1);

require_once __DIR__ . "/bdd.php";

header("Content-Type: text/plain; charset=utf-8");

try {

    $bdd = getBD();

    /* =========================================================
     * 1) Récupération du conseil du jour
     * ========================================================= */

    // Index du jour dans l'année (0 à 365)
    $dayIndex = (int) date('z');

    // Compter le nombre total de conseils
    $countStmt = $bdd->query("SELECT COUNT(*) FROM conseils");
    $totalConseils = (int) $countStmt->fetchColumn();

    if ($totalConseils === 0) {
        throw new Exception("Aucun conseil enregistré.");
    }

    // Éviter un OFFSET hors limite
    $offset = $dayIndex % $totalConseils;

    $stmt = $bdd->prepare("SELECT texte FROM conseils LIMIT 1 OFFSET :offset");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $conseil = $stmt->fetchColumn();

    if (!$conseil) {
        throw new Exception("Conseil introuvable.");
    }

    /* =========================================================
     * 2) Récupération des emails newsletter
     * ========================================================= */

    $emails = $bdd->query("SELECT email FROM newsletter_users")
                  ->fetchAll(PDO::FETCH_COLUMN);

    if (!$emails) {
        echo "Aucun utilisateur abonné.";
        exit;
    }

    /* =========================================================
     * 3) Envoi des emails
     * ========================================================= */

    $sentCount = 0;

    foreach ($emails as $email) {

        // Validation email avant envoi
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            continue;
        }

        $subject = "Conseil du jour 🍃 - NAHA";
        $headers = "From: no-reply@naha-app.fr\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $message = "Bonjour,\n\n";
        $message .= "Voici ton conseil du jour :\n\n";
        $message .= $conseil . "\n\n";
        $message .= "À demain 🍃\n";
        $message .= "L’équipe NAHA";

        $sent = mail($email, $subject, $message, $headers);

        if ($sent) {
            $sentCount++;
        }
    }

    echo "Newsletter envoyée à {$sentCount} utilisateur(s).";

} catch (Exception $e) {

    error_log("Erreur newsletter : " . $e->getMessage());

    http_response_code(500);
    echo "Erreur serveur lors de l'envoi.";
}