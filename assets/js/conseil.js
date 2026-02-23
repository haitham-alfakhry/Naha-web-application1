/**
 * ======================================================
 *  NAHA - Newsletter AJAX Handler
 *  File: conseil.js
 *  Description:
 *  - Envoi du formulaire newsletter via Fetch API
 *  - Gestion des états (loading / succès / erreur)
 *  - Protection contre double soumission
 * ======================================================
 */

document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector("#newsletter-form");
    const msg  = document.querySelector("#newsletter-msg");

    // Sécurité : si le formulaire n'existe pas on stoppe
    if (!form || !msg) return;

    form.addEventListener("submit", async (e) => {

        e.preventDefault();

        // Prévention double submit
        const submitBtn = form.querySelector("button");
        if (submitBtn) submitBtn.disabled = true;

        // Etat loading
        msg.textContent = "Envoi en cours...";
        msg.style.color = "#6b7280";

        try {

            const data = new FormData(form);

            const response = await fetch("conseil.php?ajax=newsletter", {
                method: "POST",
                body: data
            });

            // Vérification réponse HTTP
            if (!response.ok) {
                throw new Error("Erreur serveur");
            }

            const json = await response.json();

            // Affichage message serveur
            msg.textContent = json.msg || "Réponse invalide.";

            if (json.ok) {
                msg.style.color = "#45c778"; // vert succès
                form.reset();
            } else {
                msg.style.color = "#ef4444"; // rouge erreur
            }

        } catch (error) {

            console.error("Erreur Newsletter NAHA :", error);

            msg.textContent = "Impossible d'envoyer la demande. Réessaie plus tard.";
            msg.style.color = "#ef4444";

        } finally {

            if (submitBtn) submitBtn.disabled = false;

        }

    });

});