<?php
/**
 * ============================================================
 * NAHA - Page de Connexion
 * Fichier : seconnecter.php
 * Description :
 *  - Affiche le formulaire de connexion utilisateur
 *  - Validation côté client avec JavaScript
 *  - Envoi des identifiants en AJAX vers login_api.php
 *  - Gestion dynamique des erreurs sans rechargement
 *
 * Author : HAITHAM ALFAKHRY
 * ============================================================
 */

session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Connexion - NAHA</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="accueil-style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="auth-style.css?v=<?php echo time(); ?>">
</head>
<body>

<?php include "header.php"; ?>

<main class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Connexion</h1>

        <!-- Zone d'affichage des erreurs -->
        <div id="login-error" style="
            color:#e11d48;
            font-weight:600;
            margin-bottom:10px;
            display:none;
        "></div>

        <form class="auth-form" novalidate>
            <label for="mail">Adresse e-mail</label>
            <input type="email" name="mail" id="mail" required />

            <label for="mdp">Mot de passe</label>
            <input type="password" name="pswrd" id="mdp" required />

            <button type="submit" class="btn btn-primary btn-full">
                Se connecter
            </button>
        </form>

        <p class="auth-alt">
            Pas encore de compte ?
            <a href="sinscrire.php">Inscrivez-vous</a>
        </p>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector(".auth-form");
    const mail = document.querySelector("#mail");
    const mdp  = document.querySelector("#mdp");
    const errorDiv = document.querySelector("#login-error");

    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    function shakeForm() {
        form.classList.add("shake");
        setTimeout(() => form.classList.remove("shake"), 400);
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        errorDiv.style.display = "none";
        errorDiv.textContent = "";

        const emailVal = mail.value.trim();
        const passVal  = mdp.value.trim();

        if (!isValidEmail(emailVal)) {
            errorDiv.textContent = "Veuillez entrer une adresse e-mail valide.";
            errorDiv.style.display = "block";
            shakeForm();
            return;
        }

        if (passVal.length < 1) {
            errorDiv.textContent = "Mot de passe requis.";
            errorDiv.style.display = "block";
            shakeForm();
            return;
        }

        const data = new FormData();
        data.append("mail", emailVal);
        data.append("pswrd", passVal);

        try {
            const res = await fetch("login_api.php", {
                method: "POST",
                body: data
            });

            const json = await res.json();

            if (!json.success) {
                errorDiv.textContent = json.msg;
                errorDiv.style.display = "block";
                shakeForm();
                return;
            }

            window.location.href = json.redirect;

        } catch (err) {
            errorDiv.textContent = "Erreur réseau. Réessaie.";
            errorDiv.style.display = "block";
        }
    });
});
</script>

<style>
/* Animation SHAKE */
.shake {
    animation: shake 0.35s;
}
@keyframes shake {
    0% { transform: translateX(0px); }
    20% { transform: translateX(-6px); }
    40% { transform: translateX(6px); }
    60% { transform: translateX(-4px); }
    80% { transform: translateX(4px); }
    100% { transform: translateX(0px); }
}
</style>

<?php include 'footer.php'; ?>

</body>
</html>