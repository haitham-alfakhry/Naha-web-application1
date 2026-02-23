<?php
/**
 * ============================================================
 * NAHA - API Save Goal
 * Fichier : save-goal.php
 * Description :
 *  - Enregistre l'objectif calorique utilisateur
 *  - Reçoit les données en JSON (fetch API)
 *  - Supprime l'ancien objectif avant insertion
 *  - Vérifie que l'utilisateur est connecté
 *  - Retourne une réponse JSON (succès / erreur)
 *
 * Author : HAITHAM ALFAKHRY
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Vérifier session
if (!isset($_SESSION['utilisateur'])) {
    echo json_encode([
        "ok" => false,
        "message" => "Non connecté"
    ]);
    exit;
}

require_once 'bdd.php';
$bdd = getBD();

// Lire le JSON envoyé par fetch()
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode([
        "ok" => false,
        "message" => "Aucune donnée reçue"
    ]);
    exit;
}

$idUser = (int) $_SESSION['utilisateur']['id_utilisateur'];

$age      = $input['age'] ?? null;
$taille   = $input['taille'] ?? null;
$poids    = $input['poids'] ?? null;
$activite = $input['activite'] ?? null;
$sexe     = $input['sexe'] ?? null;
$maint    = $input['maintenance'] ?? null;
$nom      = $input['objectif_nom'] ?? null;
$objectif = $input['objectif_kcal'] ?? null;

/* ===== Validation minimale ===== */
if (!$maint || !$nom || !$objectif) {
    echo json_encode([
        "ok" => false,
        "message" => "Données incomplètes"
    ]);
    exit;
}

try {

    // Création table si elle n'existe pas
    $bdd->exec("
        CREATE TABLE IF NOT EXISTS objectif_utilisateur (
          id INT AUTO_INCREMENT PRIMARY KEY,
          id_utilisateur INT NOT NULL,
          objectif_nom VARCHAR(50),
          objectif_kcal INT,
          maintenance INT,
          age INT,
          poids INT,
          taille INT,
          activite FLOAT,
          sexe CHAR(1),
          date_maj DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Supprimer ancien objectif utilisateur
    $sqlDel = "DELETE FROM objectif_utilisateur WHERE id_utilisateur = ?";
    $bdd->prepare($sqlDel)->execute([$idUser]);

    // Insérer nouveau objectif
    $sql = "
        INSERT INTO objectif_utilisateur
        (id_utilisateur, objectif_nom, objectif_kcal, maintenance, age, poids, taille, activite, sexe)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $bdd->prepare($sql)->execute([
        $idUser,
        $nom,
        $objectif,
        $maint,
        $age,
        $poids,
        $taille,
        $activite,
        $sexe
    ]);

    echo json_encode([
        "ok" => true,
        "message" => "Objectif enregistré"
    ]);
}
catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "ok" => false,
        "message" => "Erreur serveur. Réessaie plus tard."
    ]);
}