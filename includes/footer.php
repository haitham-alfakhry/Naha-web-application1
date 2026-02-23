<?php
/**
 * ---------------------------------------------------------
 * Fichier : footer.php
 * Projet  : NAHA Web Application
 * Rôle    : Pied de page global du site
 * ---------------------------------------------------------
 * Ce fichier contient les informations légales
 * et la citation affichée en bas de chaque page.
 * Il est inclus dynamiquement dans toutes les pages.
 * ---------------------------------------------------------
 */
?>

<footer class="footer">
  <div class="container footer__inner">

    <!-- Citation inspirante -->
    <div class="footer__left">
      <p class="mini-quote">
        “Le futur, c’est loin. Je n’attends pas assis.”
      </p>
    </div>

    <!-- Informations légales -->
    <div class="footer__right">
      <div class="legal">
        © <?= date("Y"); ?> NAHA — Données : Open Food Facts & Compendium MET
      </div>
    </div>

  </div>
</footer>