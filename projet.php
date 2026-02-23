<?php
/**
 * ============================================================
 * NAHA - Page Projet
 * Fichier : projet.php
 * Description :
 *  - Présentation complète du projet NAHA
 *  - Section équipe
 *  - Formulaire feedback sécurisé en AJAX
 *  - Protection CSRF intégrée
 *
 * Author : HAITHAM ALFAKHRY
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ============================================================
 * 1️⃣  GÉNÉRATION TOKEN CSRF
 * ============================================================ */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* ============================================================
 * 2️⃣  ENDPOINT AJAX : FEEDBACK
 * ============================================================ */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'feedback') {

    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Méthode non autorisée.']);
        exit;
    }

    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Token CSRF invalide.']);
        exit;
    }

    $message = trim($_POST['message'] ?? '');
    if ($message === '' || mb_strlen($message) > 600) {
        echo json_encode(['ok' => false, 'error' => 'Message vide ou trop long.']);
        exit;
    }

    $userName = 'Invité';
    if (!empty($_SESSION['utilisateur']['prenom'])) {
        $userName = $_SESSION['utilisateur']['prenom'];
    }

    echo json_encode([
        'ok'  => true,
        'msg' => 'Merci pour ton retour, ' .
                 htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') .
                 ' 🙌'
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NAHA — Le Projet</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700;800&display=swap" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="accueil-style.css">
  <link rel="stylesheet" href="projet-style.css">
</head>
<body>

<?php include "header.php"; ?>

<main class="projet-page">

  <!-- HERO -->
  <section class="projet-hero">
    <div class="container projet-hero__inner">
      <p class="projet-tag">Le Projet</p>
      <h1 class="projet-title">Le Projet NAHA</h1>
      <p class="projet-subtitle">
        NAHA t’aide à équilibrer <strong>nutrition</strong>,
        <strong>sport</strong> et <strong>mental</strong>
        pour atteindre tes objectifs durablement.
      </p>
    </div>
  </section>

  <div class="divider"></div>

  <!-- VISION -->
  <section class="projet-section">
    <div class="container">
      <h2 class="section-title">Notre vision</h2>

      <div class="vision-cards">

        <article class="vision-card">
          <h3>Besoins & problèmes</h3>
          <p>
            Beaucoup de sportifs manquent de repères sur leurs
            consommations et dépenses journalières.
            NAHA apporte un cadre clair et compréhensible.
          </p>
        </article>

        <article class="vision-card">
          <h3>Usagers</h3>
          <p>
            Débutants, sportifs réguliers ou personnes cherchant
            simplement à mieux comprendre leur équilibre quotidien.
          </p>
        </article>

        <article class="vision-card">
          <h3>Notre différence</h3>
          <p>
            NAHA rend les données accessibles et actionnables,
            sans complexité inutile.
          </p>
        </article>

      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- ÉQUIPE -->
  <section class="projet-section team-section">
    <div class="container">
      <h2 class="section-title">Notre équipe</h2>

      <div class="team-grid">

        <article class="team-card">
          <span class="team-initials">AF</span>
          <h3>Arthur Feschet</h3>
          <p>Développement & architecture</p>
        </article>

        <article class="team-card">
          <span class="team-initials">NC</span>
          <h3>Noah Chayrigues</h3>
          <p>Produit & expérience utilisateur</p>
        </article>

        <article class="team-card">
          <span class="team-initials">AB</span>
          <h3>Ahmed Bekakria</h3>
          <p>Back-end & base de données</p>
        </article>

        <article class="team-card">
          <span class="team-initials">HA</span>
          <h3>Haitham Alfakhry</h3>
          <p>Front-end & intégration</p>
        </article>

      </div>
    </div>
  </section>

  <div class="divider"></div>

  <!-- FEEDBACK -->
  <section class="projet-section projet-feedback">
    <div class="container">
      <h2 class="section-title">Ton avis compte</h2>

      <form id="feedback-form" class="feedback-form">
        <textarea
          name="message"
          rows="4"
          maxlength="600"
          placeholder="Propose une idée ou amélioration..."
          required></textarea>

        <input type="hidden"
               name="csrf"
               value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8'); ?>">

        <button type="submit" class="btn big">
          Envoyer mon idée
        </button>
      </form>

      <p id="feedback-status" class="feedback-status"></p>
    </div>
  </section>

</main>

<?php include "footer.php"; ?>

<script src="projet-script.js" defer></script>

</body>
</html>