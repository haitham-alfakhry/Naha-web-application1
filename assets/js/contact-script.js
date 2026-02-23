/**
 * ============================================================
 * NAHA - Contact Form Script
 * Fichier : contact-script.js
 * Description :
 * - Validation côté serveur via AJAX
 * - Gestion erreurs par champ
 * - Feedback utilisateur dynamique
 * - Protection contre double soumission
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', () => {

    /* ============================================================
       Sélection des éléments DOM
    ============================================================ */
    const form          = document.getElementById('contact-form');
    const submitBtn     = document.getElementById('contact-submit');
    const feedbackBlock = document.getElementById('form-feedback');

    // Sécurité : si le formulaire n'existe pas → on stoppe
    if (!form || !submitBtn) return;

    /* ============================================================
       Zones d'erreur par champ
    ============================================================ */
    const errorElems = {
        nom: document.querySelector('[data-error-for="nom"]'),
        email: document.querySelector('[data-error-for="email"]'),
        sujet: document.querySelector('[data-error-for="sujet"]'),
        message: document.querySelector('[data-error-for="message"]'),
    };

    /**
     * Nettoie tous les messages d'erreur affichés
     */
    function clearErrors() {
        for (const key in errorElems) {
            if (errorElems[key]) {
                errorElems[key].textContent = '';
            }
        }

        if (feedbackBlock) {
            feedbackBlock.textContent = '';
            feedbackBlock.style.color = '';
        }
    }

    /* ============================================================
       Soumission du formulaire (AJAX)
    ============================================================ */
    form.addEventListener('submit', async (e) => {

        e.preventDefault();
        clearErrors();

        // Désactivation bouton pour éviter double envoi
        submitBtn.disabled = true;
        const originalLabel = submitBtn.textContent;
        submitBtn.textContent = 'Envoi en cours...';

        const formData = new FormData(form);

        try {

            const response = await fetch('contact.php?ajax=contact', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            // Vérification HTTP
            if (!response.ok) {
                throw new Error('Erreur serveur HTTP');
            }

            const data = await response.json();

            /* ============================================================
               Gestion des erreurs de validation
            ============================================================ */
            if (!data.ok) {

                // Erreurs par champ
                if (data.errors) {
                    Object.entries(data.errors).forEach(([field, message]) => {
                        if (errorElems[field]) {
                            errorElems[field].textContent = message;
                        }
                    });
                }

                // Erreur globale
                if (data.error && feedbackBlock) {
                    feedbackBlock.textContent = data.error;
                    feedbackBlock.style.color = '#ef4444';
                }

            } else {

                /* ============================================================
                   Succès
                ============================================================ */
                if (feedbackBlock) {
                    feedbackBlock.textContent = data.message || 
                        'Message envoyé avec succès.';
                    feedbackBlock.style.color = '#22c55e';
                }

                form.reset();
            }

        } catch (err) {

            console.error('Erreur Contact NAHA :', err);

            if (feedbackBlock) {
                feedbackBlock.textContent =
                    'Erreur réseau, vérifie ta connexion.';
                feedbackBlock.style.color = '#ef4444';
            }

        } finally {

            // Réactivation bouton
            submitBtn.disabled = false;
            submitBtn.textContent = originalLabel;
        }

    });

});