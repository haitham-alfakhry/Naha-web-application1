<?php
/**
 * ============================================================
 * NAHA - Inscription Utilisateur
 * Fichier : inscription.php
 * Description :
 *  - Traite le formulaire d'inscription
 *  - Vérifie les champs obligatoires
 *  - Vérifie correspondance des mots de passe
 *  - Vérifie unicité de l’email
 *  - Hash sécurisé du mot de passe
 *  - Redirection propre avec messages d’erreur
 *
 * Author : HAITHAM ALFAKHRY
 * ============================================================
 */

session_start();
require_once 'bdd.php';

$bdd = getBD();

/* ============================================================
 * 1️⃣ Vérification méthode POST
 * ============================================================ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: accueil.php");
    exit;
}

/* ============================================================
 * 2️⃣ Vérification champs obligatoires
 * ============================================================ */
if (
    empty($_POST['nom']) ||
    empty($_POST['prenom']) ||
    empty($_POST['mail']) ||
    empty($_POST['adresse']) ||
    empty($_POST['numero']) ||
    empty($_POST['mdp1']) ||
    empty($_POST['mdp2'])
) {
    header("Location: sinscrire.php?err=" . urlencode("Veuillez remplir tous les champs."));
    exit;
}

/* ============================================================
 * 3️⃣ Nettoyage des données
 * ============================================================ */
$nom     = trim($_POST['nom']);
$prenom  = trim($_POST['prenom']);
$mail    = trim($_POST['mail']);
$adresse = trim($_POST['adresse']);
$numero  = trim($_POST['numero']);
$mdp1    = $_POST['mdp1'];
$mdp2    = $_POST['mdp2'];

/* ============================================================
 * 4️⃣ Validation email
 * ============================================================ */
if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    header("Location: sinscrire.php?err=" . urlencode("Email invalide."));
    exit;
}

/* ============================================================
 * 5️⃣ Vérification mots de passe
 * ============================================================ */
if ($mdp1 !== $mdp2) {
    header("Location: sinscrire.php?err=" . urlencode("Les mots de passe ne correspondent pas.")
        . "&nom=" . urlencode($nom)
        . "&prenom=" . urlencode($prenom)
        . "&mail=" . urlencode($mail)
        . "&adresse=" . urlencode($adresse)
        . "&numero=" . urlencode($numero)
    );
    exit;
}

/* ============================================================
 * 6️⃣ Vérifier si email déjà utilisé
 * ============================================================ */
$sql = "SELECT id_utilisateur FROM utilisateurs WHERE mail = :mail LIMIT 1";
$stmt = $bdd->prepare($sql);
$stmt->execute(['mail' => $mail]);

if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    header("Location: sinscrire.php?err=" . urlencode("Cette adresse e-mail est déjà utilisée.")
        . "&nom=" . urlencode($nom)
        . "&prenom=" . urlencode($prenom)
        . "&adresse=" . urlencode($adresse)
        . "&numero=" . urlencode($numero)
        . "&mail=" . urlencode($mail)
    );
    exit;
}

/* ============================================================
 * 7️⃣ Hash sécurisé du mot de passe
 * ============================================================ */
$hash = password_hash($mdp1, PASSWORD_DEFAULT);

/* ============================================================
 * 8️⃣ Insertion utilisateur
 * ============================================================ */
$sql = "INSERT INTO utilisateurs (nom, prenom, numero, adresse, mail, pswrd)
        VALUES (:nom, :prenom, :numero, :adresse, :mail, :pswrd)";

$stmt = $bdd->prepare($sql);
$stmt->execute([
    'nom'    => $nom,
    'prenom' => $prenom,
    'numero' => $numero,
    'adresse'=> $adresse,
    'mail'   => $mail,
    'pswrd'  => $hash
]);

/* ============================================================
 * 9️⃣ Redirection vers connexion
 * ============================================================ */
header("Location: seconnecter.php?success=1");
exit;