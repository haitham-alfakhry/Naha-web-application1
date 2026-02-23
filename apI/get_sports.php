<?php
/**
 * ============================================================
 * NAHA - API Get Sports
 * Fichier : api/get_sports.php
 * Description :
 *  - Retourne la liste des sports (JSON)
 *  - Accès réservé aux utilisateurs connectés
 *  - Gestion d'erreurs propre (sans exposer détails serveur)
 * ============================================================
 */

declare(strict_types=1);

session_start();

// Header JSON systématique
header('Content-Type: application/json; charset=utf-8');

// ============================================================
// Vérification authentification
// ============================================================
if (!isset($_SESSION['utilisateur'])) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'error' => 'Utilisateur non authentifié.'
    ]);
    exit;
}

// ============================================================
// Chargement BDD
// ============================================================
require_once __DIR__ . '/../bdd.php';

try {

    $bdd = getBD();

    $sql = "
        SELECT 
            id_sport,
            nom_sport,
            MET,
            kcal_h_60kg,
            kcal_h_70kg,
            kcal_h_80kg
        FROM sports
        ORDER BY nom_sport ASC
    ";

    $stmt = $bdd->query($sql);
    $sports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok' => true,
        'data' => $sports
    ]);

} catch (PDOException $e) {

    // Log serveur (visible uniquement côté serveur)
    error_log('Erreur get_sports.php : ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Erreur interne du serveur.'
    ]);
}