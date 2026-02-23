<?php
/**
 * ============================================================
 * NAHA - Update Profil
 * Fichier : api/update_profil.php
 * Author : HAITHAM ALFAKHRY
 * Description :
 *  - Mise à jour infos utilisateur
 *  - Upload sécurisé avatar
 *  - Insertion objectif utilisateur
 *  - Protection session & validation
 * ============================================================
 */

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../bdd.php';

header('Content-Type: text/html; charset=utf-8');

// ============================================================
// Vérification authentification
// ============================================================
if (!isset($_SESSION['utilisateur']['id'])) {
    header("Location: ../seconnecter.php");
    exit;
}

$id = (int) $_SESSION['utilisateur']['id'];

// ============================================================
// Vérification méthode POST
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../profil.php");
    exit;
}

// ============================================================
// Nettoyage & validation
// ============================================================
$nom     = trim($_POST['nom'] ?? '');
$prenom  = trim($_POST['prenom'] ?? '');
$age     = (int) ($_POST['age'] ?? 0);
$poids   = (float) ($_POST['poids'] ?? 0);
$taille  = (float) ($_POST['taille'] ?? 0);

if ($nom === '' || $prenom === '') {
    header("Location: ../profil.php?error=Champs invalides");
    exit;
}

// ============================================================
// Connexion BDD
// ============================================================
$bdd = getBD();

try {

    // ========================================================
    // Upload Avatar sécurisé
    // ========================================================
    $avatarPath = null;

    if (!empty($_FILES['avatar']['name'])) {

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = mime_content_type($_FILES['avatar']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Format image non autorisé.");
        }

        if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) {
            throw new Exception("Image trop volumineuse (max 2MB).");
        }

        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $fileName  = uniqid('avatar_', true) . '.' . $extension;
        $targetDir = __DIR__ . '/../uploads/';
        $target    = $targetDir . $fileName;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
            throw new Exception("Erreur upload fichier.");
        }

        $avatarPath = 'uploads/' . $fileName;
    }

    // ========================================================
    // Mise à jour utilisateur
    // ========================================================
    $stmt = $bdd->prepare("
        UPDATE utilisateurs 
        SET nom = ?, 
            prenom = ?, 
            avatar = IFNULL(?, avatar)
        WHERE id_utilisateur = ?
    ");

    $stmt->execute([$nom, $prenom, $avatarPath, $id]);

    // ========================================================
    // Mise à jour objectif (INSERT simple ici)
    // ========================================================
    $stmt2 = $bdd->prepare("
        INSERT INTO objectif_utilisateur
        (id_utilisateur, age, poids, taille, activite, sexe,
         objectif_nom, objectif_kcal, maintenance, date_maj)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt2->execute([
        $id,
        $age,
        $poids,
        $taille,
        1,
        'H',
        'Mise à jour',
        0,
        0
    ]);

    header("Location: ../profil.php?success=1");
    exit;

} catch (Exception $e) {

    error_log("Erreur update_profil : " . $e->getMessage());

    header("Location: ../profil.php?error=Erreur serveur");
    exit;
}