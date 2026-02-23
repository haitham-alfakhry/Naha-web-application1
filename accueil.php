<?php
/**
 * ============================================================
 * NAHA - Page Accueil
 * Fichier : accueil.php
 * Description :
 *  - Page principale publique de l'application NAHA
 *  - Affiche la mission, KPIs, features et newsletter
 *  - Adapte l'affichage selon l'état de connexion
 *  - Utilise Chart.js + animations JS personnalisées
 *
 * Author : HAITHAM ALFAKHRY
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>NAHA — Accueil</title>

  <!-- ================= Fonts ================= -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700;800&display=swap" rel="stylesheet">

  <!-- ================= Styles ================= -->
  <!-- Fichier CSS principal -->
  <link rel="stylesheet" href="assets/css/accueil-style.css" />

</head>

<body>

<!-- ================= Header ================= -->
<?php include "includes/header.php"; ?>


<main id="top">

  <!-- ===================================================== -->
  <!-- HERO SECTION -->
  <!-- ===================================================== -->
  <section class="hero" data-parallax>
    <div class="container hero__inner" data-animate="fade-up">
      <h1 class="hero__title">NAHA</h1>

      <p class="hero__quote">
        “Les grands accomplissements sont réussis non par la force, mais par la persévérance.”<br>
        <span>Samuel Johnson</span>
      </p>

      <!-- CTA dynamique selon connexion -->
      <div class="hero__cta">
        <?php if (isset($_SESSION['utilisateur'])): ?>
          <a class="btn big" href="tableau.php">Accéder au tableau de bord</a>
          <a class="btn ghost" href="#features">Découvrir</a>
        <?php else: ?>
          <a class="btn big" href="sinscrire.php">Commencer</a>
          <a class="btn ghost" href="seconnecter.php">Se connecter</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Barre de progression scroll -->
    <div class="scroll-progress"></div>
  </section>

  <div class="divider"></div>

  <!-- ===================================================== -->
  <!-- MISSION -->
  <!-- ===================================================== -->
  <section class="mission" data-animate="fade-up" id="projet">
    <div class="container">

      <h2 class="title">Notre mission</h2>

      <div class="mission__row" data-animate="fade-right">
        <div class="mission__text">
          <p>
            Chez <strong>NAHA</strong>, on veut aider chaque sportif à mieux se comprendre,
            à progresser jour après jour et à garder la motivation.<br>
            Nos outils statistiques sont faits pour t’accompagner, simplement.
          </p>

          <ul class="ticks">
            <li>Suivi clair de tes séances</li>
            <li>Visualisations utiles et simples</li>
            <li>Conseils actionnables chaque semaine</li>
          </ul>
        </div>

        <!-- Graphiques dynamiques -->
        <div class="mission__art" data-animate="fade-left">
          <div class="chart-card">
            <canvas height="160" data-lazy-chart="bar"></canvas>
          </div>

          <div class="chart-row">
            <div class="chart-card small">
              <canvas height="110" data-lazy-chart="gauge"></canvas>
            </div>
            <div class="chart-card small">
              <canvas height="110" data-lazy-chart="mini"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="mission__row mission__row--alt" data-animate="fade-up">
        <div class="mission__art">
          <div class="chart-card soft">
            <canvas height="130" data-lazy-chart="area"></canvas>
          </div>
        </div>

        <div class="mission__quote">
          <p>
            “Chaque calorie compte, chaque effort te rapproche de ta meilleure version.<br>
            La donnée, c’est notre langage — la persévérance, notre philosophie.”
          </p>
        </div>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- ===================================================== -->
  <!-- KPIs -->
  <!-- ===================================================== -->
  <section class="kpis" id="dash" data-animate="fade-up">
    <div class="container kpis__grid">
      <div class="kpi">
        <div class="kpi__num" data-counter="1250">0</div>
        <div class="kpi__label">Séances suivies</div>
      </div>
      <div class="kpi">
        <div class="kpi__num" data-counter="87">0</div>
        <div class="kpi__label">Programmes actifs</div>
      </div>
      <div class="kpi">
        <div class="kpi__num" data-counter="98">0</div>
        <div class="kpi__label">Satisfaction (%)</div>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- ===================================================== -->
  <!-- FEATURES -->
  <!-- ===================================================== -->
  <section class="features" id="features" data-animate="fade-up">
    <div class="container">
      <h3 class="features__title">
        Découvrez notre calculateur performant et<br>
        des entraînements personnalisés
      </h3>

      <div class="features__grid">
        <a class="cta" href="calculateur.php">Calculateur</a>
        <a class="cta cta--right" href="contact.php">Contactez-nous</a>
        <a class="cta" href="tableau.php">Tableau de bord</a>
        <a class="cta cta--right" href="#train">Entraînement</a>
      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- ===================================================== -->
  <!-- NEWSLETTER -->
  <!-- ===================================================== -->
  <section class="newsletter" data-animate="fade-up">
    <div class="container newsletter__card">

      <h3>Reste dans le flow 📈</h3>
      <p>Reçois 1 tip data & perf par semaine.</p>

      <form id="newsletter-form" class="newsletter-form" method="POST" action="newsletter.php">
        <input type="email" name="email" class="newsletter-input"
               placeholder="tonemail@exemple.com" required>
        <button type="submit" class="btn">S’abonner</button>
      </form>

      <?php if (!empty($_SESSION['newsletter_msg'])): ?>
        <p class="newsletter-feedback">
          <?= htmlspecialchars($_SESSION['newsletter_msg']) ?>
        </p>
        <?php unset($_SESSION['newsletter_msg']); ?>
      <?php endif; ?>

    </div>
  </section>

  <div class="divider"></div>

  <!-- ===================================================== -->
  <!-- TEAM -->
  <!-- ===================================================== -->
  <section class="team" data-animate="fade-up">
    <div class="container">
      <h2 class="title title--spaced">DÉCOUVREZ NOTRE ÉQUIPE</h2>
      <div class="team__list">
        <span>Haitham</span>
        <span>Ahmed</span>
        <span>Noah</span>
        <span>Arthur</span>
      </div>
    </div>
  </section>

</main>

<!-- ================= Footer ================= -->
<?php include 'includes/footer.php'; ?>


<!-- ================= Scripts ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/accueil-script.js"></script>

</body>
</html>