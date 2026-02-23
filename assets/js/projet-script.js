/**
 * ============================================================
 * NAHA - Projet Page Script
 * Fichier : projet-script.js
 * Description :
 * - Gestion AJAX du feedback utilisateur
 * - Cartes interactives (Vision & Équipe)
 * - Smooth scroll interne
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', () => {

  /* =========================================================
     1) FEEDBACK AJAX
  ========================================================= */
  const feedbackForm = document.getElementById('feedback-form');
  const feedbackMsg  = document.getElementById('feedback-status');

  if (feedbackForm && feedbackMsg) {

    feedbackForm.addEventListener('submit', async (e) => {

      e.preventDefault();

      const submitBtn = feedbackForm.querySelector('button');
      if (submitBtn) submitBtn.disabled = true;

      const formData = new FormData(feedbackForm);
      const message  = (formData.get('message') || '').toString().trim();

      if (!message) {
        feedbackMsg.textContent = 'Écris au moins une petite phrase 😉';
        feedbackMsg.style.color = '#b91c1c';
        if (submitBtn) submitBtn.disabled = false;
        return;
      }

      feedbackMsg.textContent = 'Envoi en cours...';
      feedbackMsg.style.color = '#4b5563';

      try {

        const response = await fetch('projet.php?ajax=feedback', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        });

        if (!response.ok) {
          throw new Error('Erreur HTTP serveur');
        }

        const data = await response.json();

        if (data.ok) {
          feedbackMsg.textContent = data.msg || 'Merci pour ton retour 🙌';
          feedbackMsg.style.color = '#15803d';
          feedbackForm.reset();
        } else {
          feedbackMsg.textContent = data.error || 'Erreur lors de l’envoi.';
          feedbackMsg.style.color = '#b91c1c';
        }

      } catch (err) {

        console.error('Erreur Feedback NAHA :', err);

        feedbackMsg.textContent = 'Erreur réseau. Réessaie dans un instant.';
        feedbackMsg.style.color = '#b91c1c';

      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }

    });
  }

  /* =========================================================
     2) VISION - Cartes interactives
  ========================================================= */
  const visionCards = document.querySelectorAll('.vision-card');

  visionCards.forEach((card) => {

    // Hover animation
    card.addEventListener('mouseenter', () => {
      visionCards.forEach(c => c.classList.remove('is-hovered'));
      card.classList.add('is-hovered');
    });

    card.addEventListener('mouseleave', () => {
      card.classList.remove('is-hovered');
    });

    // Pin au clic
    card.addEventListener('click', () => {

      const alreadyPinned = card.classList.contains('is-pinned');
      visionCards.forEach(c => c.classList.remove('is-pinned'));

      if (!alreadyPinned) {
        card.classList.add('is-pinned');
      }
    });

  });

  /* =========================================================
     3) ÉQUIPE - Focus sur carte
  ========================================================= */
  const teamCards = document.querySelectorAll('.team-card');

  teamCards.forEach((card) => {

    card.addEventListener('mouseenter', () => {
      card.classList.add('is-hovered');
    });

    card.addEventListener('mouseleave', () => {
      card.classList.remove('is-hovered');
    });

    card.addEventListener('click', () => {

      const isActive = card.classList.contains('is-active');
      teamCards.forEach(c => c.classList.remove('is-active'));

      if (!isActive) {
        card.classList.add('is-active');
      }
    });

  });

  /* =========================================================
     4) Smooth Scroll interne
  ========================================================= */
  document.querySelectorAll('a[href^="#"]').forEach((link) => {

    link.addEventListener('click', (e) => {

      const id = link.getAttribute('href');

      if (id && id.length > 1) {

        const target = document.querySelector(id);

        if (target) {
          e.preventDefault();
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      }
    });

  });

});