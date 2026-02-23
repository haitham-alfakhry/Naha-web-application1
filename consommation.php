<?php
/**
 * ============================================================
 * NAHA - Journal Consommation & Activité
 * Fichier : consommation.php
 * Description :
 *  - Permet d’ajouter des consommations alimentaires
 *  - Permet d’ajouter des activités sportives
 *  - Affiche les dernières entrées utilisateur
 *  - Filtrage dynamique des selects (JS)
 *
 * Sécurité :
 *  - Session obligatoire
 *  - Requêtes préparées PDO
 *
 * Author : HAITHAM ALFAKHRY
 * ============================================================
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* ============================================================
 * 1️⃣ Sécurité + Connexion BDD
 * ============================================================ */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'bdd.php';
$bdd = getBD();

if (!isset($_SESSION['utilisateur'])) {
    header('Location: seconnecter.php');
    exit;
}

/* ID utilisateur (compatibilité ancienne structure session) */
$idUser = (int)($_SESSION['utilisateur']['id'] 
          ?? $_SESSION['utilisateur']['id_utilisateur']);


/* ============================================================
 * 2️⃣ TRAITEMENT FORMULAIRE — CONSOMMATION
 * ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_conso'])) {

    $id_produit = $_POST['id_produit'] ?? null;
    $quantite   = $_POST['quantite'] ?? null;

    if ($id_produit && $quantite > 0) {

        $sql = "INSERT INTO consommation 
                (id_utilisateur, id_produit, quantite, date_conso)
                VALUES (:u, :p, :q, NOW())";

        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            'u' => $idUser,
            'p' => $id_produit,
            'q' => $quantite
        ]);
    }
}


/* ============================================================
 * 3️⃣ TRAITEMENT FORMULAIRE — ACTIVITÉ
 * ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sport'])) {

    $id_sport = $_POST['id_sport'] ?? null;
    $duree    = $_POST['duree'] ?? null;

    if ($id_sport && $duree > 0) {

        $sql = "INSERT INTO activite 
                (id_utilisateur, id_sport, duree_minutes, date_sport)
                VALUES (:u, :s, :d, NOW())";

        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            'u' => $idUser,
            's' => $id_sport,
            'd' => $duree
        ]);
    }
}


/* ============================================================
 * 4️⃣ RÉCUPÉRATION PRODUITS + SPORTS
 * ============================================================ */
$produits = $bdd->query("
    SELECT id_produit, nom_produit, energie_kcal 
    FROM produits 
    ORDER BY nom_produit ASC
")->fetchAll(PDO::FETCH_ASSOC);

$sports = $bdd->query("
    SELECT id_sport, nom_sport, MET, kcal_h_70kg 
    FROM sports 
    ORDER BY nom_sport ASC
")->fetchAll(PDO::FETCH_ASSOC);


/* ============================================================
 * 5️⃣ DERNIÈRES ENTRÉES UTILISATEUR
 * ============================================================ */
$stmt = $bdd->prepare("
    SELECT p.nom_produit, p.energie_kcal, c.quantite, c.date_conso 
    FROM consommation c
    JOIN produits p ON c.id_produit = p.id_produit
    WHERE c.id_utilisateur = :id
    ORDER BY c.date_conso DESC
    LIMIT 10
");
$stmt->execute(['id' => $idUser]);
$consos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $bdd->prepare("
    SELECT s.nom_sport, a.duree_minutes, a.date_sport
    FROM activite a
    JOIN sports s ON s.id_sport = a.id_sport
    WHERE a.id_utilisateur = :id
    ORDER BY a.date_sport DESC
    LIMIT 10
");
$stmt->execute(['id' => $idUser]);
$activites = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>