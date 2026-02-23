<?php
/**
 * ---------------------------------------------------------
 * Fichier : header.php
 * Projet  : NAHA Web Application
 * Rôle    : En-tête global du site
 * ---------------------------------------------------------
 * Ce fichier :
 *  - Vérifie la session utilisateur
 *  - Récupère les informations essentielles (nom, avatar)
 *  - Affiche la barre de navigation dynamique
 * ---------------------------------------------------------
 */

// Démarrage sécurisé de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la connexion PDO
require_once __DIR__ . "/bdd.php";
$bdd = getBD();

/**
 * Avatar par défaut
 * Utilisé si l'utilisateur n'a pas défini de photo
 */
$avatar = "assets/img/default-avatar.jpg";
$user   = null;

/**
 * Si l'utilisateur est connecté,
 * récupération de ses informations depuis la base
 */
if (isset($_SESSION['utilisateur']['id_utilisateur'])) {

    $req = $bdd->prepare("
        SELECT nom, prenom, avatar
        FROM utilisateurs
        WHERE id_utilisateur = ?
    ");

    $req->execute([$_SESSION['utilisateur']['id_utilisateur']]);
    $user = $req->fetch(PDO::FETCH_ASSOC);

    // Si un avatar existe en base, on l'utilise
    if (!empty($user['avatar'])) {
        $avatar = $user['avatar'];
    }
}
?>

<!-- =========================
     HEADER VISUEL DU SITE
========================= -->
<header class="topbar">
    <div class="container topbar__inner">

        <!-- Logo + Nom du site -->
        <a class="brand" href="accueil.php">
            <span class="brand__logo">🍃</span>
            <span class="brand__text">NAHA</span>
        </a>

        <!-- Navigation principale -->
        <nav class="menu">
            <a class="pill" href="accueil.php">Accueil</a>
            <a class="pill" href="tableau.php">Tableau de bord</a>
            <a class="pill" href="calculateur.php">Calculateur</a>
            <a class="pill" href="projet.php">Le Projet</a>
            <a class="pill" href="consommation.php">Consommation</a>
            <a class="pill" href="contact.php">Contact</a>

            <?php if ($user): ?>
                <a class="pill" href="profil.php">Mon Profil</a>
            <?php endif; ?>
        </nav>

        <!-- Section authentification -->
        <div class="auth">

            <?php if ($user): ?>

                <!-- Avatar + Nom complet -->
                <span class="auth-user">

                    <img src="<?= htmlspecialchars($avatar) ?>"
                         alt="Avatar utilisateur"
                         style="
                            width:32px;
                            height:32px;
                            border-radius:50%;
                            object-fit:cover;
                            vertical-align:middle;
                            margin-right:5px;
                         ">

                    <span class="name-only">
                        <?= htmlspecialchars($user['prenom'] . " " . $user['nom'], ENT_QUOTES, 'UTF-8') ?>
                    </span>

                    <!-- Menu déroulant utilisateur -->
                    <div class="dropdown-menu">
                        <a href="profil.php">Mon Profil</a>
                        <a href="parametres.php">Paramètres</a>
                        <a href="includes/deconnexion.php">Déconnexion</a>
                    </div>
                </span>

                <!-- Bouton secondaire de déconnexion -->
                <a class="btn-ghost" href="includes/deconnexion.php">Déconnexion</a>

            <?php else: ?>

                <!-- Liens si non connecté -->
                <a class="link" href="seconnecter.php">Se connecter</a>
                <a class="btn" href="sinscrire.php">S’inscrire</a>

            <?php endif; ?>

        </div>

    </div>
</header>

<script>
/**
 * Gestion simple du menu déroulant utilisateur
 * Le script est protégé pour éviter les erreurs
 * si l'utilisateur n'est pas connecté.
 */
const trigger = document.querySelector(".name-only");
const menu = document.querySelector(".dropdown-menu");

if (trigger && menu) {

    trigger.addEventListener("click", () => {
        if (menu.classList.contains("show")) {
            menu.classList.remove("show");
            setTimeout(() => menu.style.display = "none", 200);
        } else {
            menu.style.display = "block";
            setTimeout(() => menu.classList.add("show"), 10);
        }
    });

    document.addEventListener("click", (e) => {
        if (!trigger.contains(e.target) && !menu.contains(e.target)) {
            if (menu.classList.contains("show")) {
                menu.classList.remove("show");
                setTimeout(() => menu.style.display = "none", 200);
            }
        }
    });
}
</script>