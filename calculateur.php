<?php
/**
 * ============================================================
 * NAHA - Calculateur Métabolique
 * Fichier : calculateur.php
 * Description :
 *  - Permet de calculer les besoins caloriques journaliers
 *  - Utilise la formule de Mifflin-St Jeor (via JS)
 *  - Permet d’enregistrer un objectif personnalisé
 *  - Accessible uniquement aux utilisateurs connectés
 *
 * Sécurité :
 *  - Vérification session utilisateur
 *  - Données traitées côté client puis envoyées via AJAX sécurisé
 *
 * Auteur : HAITHAM ALFAKHRY
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'bdd.php';

/* =============================
   Vérification utilisateur
============================= */
if (!isset($_SESSION['utilisateur'])) {
    header('Location: seconnecter.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>NAHA — Calculateur</title>

  <!-- Police principale -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700;800&display=swap" rel="stylesheet">

  <!-- Styles globaux + spécifiques -->
  <link rel="stylesheet" href="accueil-style.css" />
  <link rel="stylesheet" href="calculateur-style.css" />
</head>

<body>

<?php include "header.php"; ?>

<main class="page calc-page">

  <!-- =============================
       SECTION HERO CALCULATEUR
  ============================== -->
  <section class="calc-hero">
    <div class="container calc-grid">

      <section class="card big">

        <h1>Ton calculateur personnalisé</h1>
        <p class="sub">
          Découvre combien ton corps dépense chaque jour,
          puis laisse NAHA t’aider à organiser ton alimentation
          et tes entraînements autour de cet objectif.
        </p>

        <!-- =============================
             FORMULAIRE CALCUL
        ============================== -->
        <form id="calc-form" class="calc-form">

          <div class="form-grid">

            <div class="field">
              <label for="age">Âge</label>
              <input type="number" id="age" min="10" max="99" value="20" />
            </div>

            <div class="field">
              <label for="taille">Taille (cm)</label>
              <input type="number" id="taille" min="120" max="230" value="178" />
            </div>

            <div class="field">
              <label for="poids">Poids (kg)</label>
              <input type="number" id="poids" min="30" max="200" value="72" />
            </div>

            <div class="field">
              <label for="activite">Niveau d’activité</label>
              <select id="activite">
                <option value="1.2">Sédentaire — activité faible</option>
                <option value="1.375">Léger — 1-2 séances / semaine</option>
                <option value="1.55" selected>Modéré — 3-4 séances / semaine</option>
                <option value="1.725">Intense — 4-6 séances / semaine</option>
                <option value="1.9">Très intense — 7 séances / semaine</option>
              </select>
            </div>

          </div>

          <!-- Sélection du sexe -->
          <div class="field">
            <span>Sexe</span>
            <input type="hidden" id="sexe" value="H" />
            <div class="segmented">
              <button type="button" class="segmented-btn is-active" data-sexe="H">Homme</button>
              <button type="button" class="segmented-btn" data-sexe="F">Femme</button>
            </div>
          </div>

          <button type="submit" class="btn big">
            Calculer mes besoins
          </button>

        </form>

        <!-- =============================
             RÉSULTATS
        ============================== -->
        <div class="result-card">

          <h2>Résultat</h2>

          <p class="res-main">
            Maintenance : <span id="res-kcal">–</span> kcal / jour
          </p>

          <p class="res-text" id="res-text">
            Estimation basée sur la formule de Mifflin-St Jeor.
          </p>

          <!-- =============================
               OBJECTIFS
          ============================== -->
          <div class="goals">

            <button type="button" class="goal-btn is-active"
                    data-delta="0" data-name="Maintien">
              <span class="goal-label">MAINTIEN</span>
              <span class="goal-kcal" id="goal-maintien">–</span>
              <span class="goal-active-text">Objectif actuel</span>
              <span class="goal-sub">Rester au même poids.</span>
            </button>

            <button type="button" class="goal-btn"
                    data-delta="-400" data-name="Perte de poids">
              <span class="goal-label">PERTE</span>
              <span class="goal-kcal" id="goal-perte">–</span>
              <span class="goal-active-text">Objectif actuel</span>
              <span class="goal-sub">Déficit léger et durable.</span>
            </button>

            <button type="button" class="goal-btn"
                    data-delta="300" data-name="Prise de masse">
              <span class="goal-label">PRISE</span>
              <span class="goal-kcal" id="goal-prise">–</span>
              <span class="goal-active-text">Objectif actuel</span>
              <span class="goal-sub">Excédent contrôlé.</span>
            </button>

          </div>

          <!-- Enregistrement -->
          <button type="button" class="btn small outline" id="save-goal">
            Enregistrer cet objectif sur mon profil
          </button>

          <p class="mini" id="save-goal-msg"></p>

          <!-- =============================
               PROCHAINES ÉTAPES
          ============================== -->
          <div class="next-steps">

            <h3>Et maintenant ?</h3>

            <div class="next-steps__grid">

              <div class="next-step">
                <h4>Suivre mes calories</h4>
                <a href="consommation.php" class="cta">
                  Ouvrir mon journal
                </a>
              </div>

              <div class="next-step">
                <h4>Voir mon tableau</h4>
                <a href="tableau.php" class="cta">
                  Tableau de bord
                </a>
              </div>

              <div class="next-step">
                <h4>Mes entraînements</h4>
                <a href="entrainements.php" class="cta">
                  Voir les séances
                </a>
              </div>

            </div>

          </div>

        </div>

      </section>
    </div>
  </section>
</main>

<?php include 'footer.php'; ?>

<!-- Script calculateur -->
<script defer src="calculateur-script.js"></script>

</body>
</html>