/**
 * ============================================================
 * NAHA - Consommation Script
 * Fichier : consommation-script.js
 * Description :
 * - Chargement dynamique des produits & sports
 * - Calcul automatique des kcal
 * - Enregistrement AJAX journal quotidien
 * ============================================================
 */

document.addEventListener("DOMContentLoaded", () => {

    /* ============================================================
       Sélection des éléments DOM
    ============================================================ */
    const prodSelect  = document.getElementById("prod-select");
    const prodQte     = document.getElementById("prod-qte");
    const prodKcal    = document.getElementById("prod-kcal");

    const sportSelect = document.getElementById("sport-select");
    const sportDuree  = document.getElementById("sport-duree");
    const sportKcal   = document.getElementById("sport-kcal");

    const logBtn      = document.getElementById("btn-log");
    const logMsg      = document.getElementById("log-msg");

    // Sécurité : si les éléments principaux n'existent pas → stop
    if (!prodSelect || !sportSelect || !logBtn) return;

    /* ============================================================
       PRODUITS
    ============================================================ */

    fetch("consommation.php?ajax=produits")
        .then(res => res.json())
        .then(rows => {

            let options = `<option value="">Sélectionner un produit</option>`;

            rows.forEach(r => {
                options += `
                    <option value="${r.id_produit}" data-kcal="${r.energie_kcal}">
                        ${r.nom_produit}
                    </option>
                `;
            });

            prodSelect.innerHTML = options;
        })
        .catch(() => {
            console.error("Erreur chargement produits");
        });

    function updateProdKcal() {

        const opt = prodSelect.selectedOptions[0];

        if (!opt || !opt.dataset.kcal) {
            prodKcal.textContent = "0";
            return;
        }

        const kcal100 = parseFloat(opt.dataset.kcal) || 0;
        const qte     = parseFloat(prodQte.value) || 0;

        const total = (kcal100 * qte) / 100;

        prodKcal.textContent = total.toFixed(0);
    }

    prodSelect.addEventListener("change", updateProdKcal);
    prodQte.addEventListener("input", updateProdKcal);

    /* ============================================================
       SPORTS
    ============================================================ */

    fetch("consommation.php?ajax=sports")
        .then(res => res.json())
        .then(rows => {

            let options = `<option value="">Sélectionner un sport</option>`;

            rows.forEach(r => {
                options += `
                    <option value="${r.id_sport}" data-k70="${r.kcal_h_70kg}">
                        ${r.nom_sport}
                    </option>
                `;
            });

            sportSelect.innerHTML = options;
        })
        .catch(() => {
            console.error("Erreur chargement sports");
        });

    function updateSportKcal() {

        const opt = sportSelect.selectedOptions[0];

        if (!opt || !opt.dataset.k70) {
            sportKcal.textContent = "0";
            return;
        }

        const kcalH = parseFloat(opt.dataset.k70) || 0;
        const duree = parseFloat(sportDuree.value) || 0;

        const total = (kcalH * duree) / 60;

        sportKcal.textContent = total.toFixed(0);
    }

    sportSelect.addEventListener("change", updateSportKcal);
    sportDuree.addEventListener("input", updateSportKcal);

    /* ============================================================
       LOG JOURNALIER
    ============================================================ */

    logBtn.addEventListener("click", () => {

        const data = new FormData();
        data.append("id_produit", prodSelect.value);
        data.append("quantite", prodQte.value);
        data.append("id_sport", sportSelect.value);
        data.append("duree", sportDuree.value);

        logMsg.textContent = "Enregistrement...";
        logMsg.className   = "log-msg";

        fetch("consommation.php?ajax=log", {
            method: "POST",
            body: data
        })
        .then(res => res.json())
        .then(r => {

            if (r.ok) {
                logMsg.textContent = "✔ Ajouté à ta journée";
                logMsg.classList.add("log-ok");
            } else {
                logMsg.textContent = "❌ " + (r.error || "Erreur");
                logMsg.classList.add("log-error");
            }

        })
        .catch(() => {
            logMsg.textContent = "❌ Erreur serveur";
            logMsg.classList.add("log-error");
        });

    });

});