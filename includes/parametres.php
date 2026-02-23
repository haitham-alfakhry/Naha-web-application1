<?php
/**
 * ---------------------------------------------------------
 * Fichier : parametres.php
 * Projet  : NAHA Web Application
 * Rôle    : Page de gestion des paramètres du compte
 * ---------------------------------------------------------
 * Cette page permet à l'utilisateur connecté de :
 *  - Modifier ses informations personnelles
 *  - Mettre à jour son adresse email
 *  - Changer son mot de passe
 * ---------------------------------------------------------
 */

// Démarrage sécurisé de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la connexion PDO
require_once "includes/bdd.php";
$bdd = getBD();

/**
 * Vérification de l'authentification
 * Si l'utilisateur n'est pas connecté, redirection vers la page de connexion
 */
if (!isset($_SESSION['utilisateur'])) {
    header("Location: seconnecter.php");
    exit;
}

// Récupération de l'identifiant utilisateur
$id = $_SESSION['utilisateur']['id_utilisateur'];

/**
 * Récupération des informations utilisateur depuis la base de données
 */
$req = $bdd->prepare("SELECT * FROM utilisateurs WHERE id_utilisateur = ?");
$req->execute([$id]);
$user = $req->fetch(PDO::FETCH_ASSOC);

// Sécurisation pour éviter les valeurs NULL
$email = $user['mail'] ?? "";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres — NAHA</title>

    <!-- Polices Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700;800&display=swap" rel="stylesheet">

    <!-- Feuilles de style -->
    <link rel="stylesheet" href="assets/css/accueil-style.css">
    <link rel="stylesheet" href="assets/css/parametres.css">
</head>

<body>

<?php include "includes/header.php"; ?>

<main class="container" style="padding:40px 0;">
    <h1 style="font-weight:800; text-align:center; margin-bottom:20px;">
        Paramètres du compte
    </h1>

    <p style="text-align:center; color:#555; margin-bottom:40px;">
        Modifiez vos informations personnelles, votre adresse email ou votre mot de passe.
    </p>

    <div class="settings-grid">

        <!-- ========================= -->
        <!-- Informations personnelles -->
        <!-- ========================= -->
        <div class="card-settings">
            <h2>Informations personnelles</h2>

            <form action="api/update_profil.php" method="POST" enctype="multipart/form-data">

                <label>Nom</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>">

                <label>Prénom</label>
                <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>">

                <label>Photo de profil</label>
                <input type="file" name="avatar" accept="image/*">

                <button class="btn">Sauvegarder</button>
            </form>
        </div>

        <!-- ===== Email ===== -->
        <div class="card-settings">
            <h2>Adresse Email</h2>

            <form action="api/update_email.php" method="POST">
                <label>Email actuel</label>
                <input type="email" value="<?= htmlspecialchars($email) ?>" disabled>

                <label>Nouvel email</label>
                <input type="email" name="new_email" required>

                <button class="btn">Modifier l’email</button>
            </form>
        </div>

        <!-- ===== Mot de passe ===== -->
        <div class="card-settings">
            <h2>Modifier le mot de passe</h2>

            <form action="api/update_password.php" method="POST">
                <label>Mot de passe actuel</label>
                <input type="password" name="old_password" required>

                <label>Nouveau mot de passe</label>
                <input type="password" name="new_password" required>

                <label>Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" required>

                <button class="btn">Changer le mot de passe</button>
            </form>
        </div>

    </div>
</main>

<?php include "includes/footer.php"; ?>

</body>
</html>