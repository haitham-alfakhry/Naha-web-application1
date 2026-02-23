/**
 * ======================================================
 *  NAHA - Calculateur Métabolique
 *  File: calculateur.js
 *  Description:
 *  - Calcul BMR (Mifflin-St Jeor)
 *  - Calcul maintenance calorique
 *  - Gestion des objectifs (maintien / perte / prise)
 *  - Sauvegarde locale (localStorage)
 *  - Enregistrement serveur via fetch API
 * ======================================================
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ======================================================
     Sélection des éléments DOM
  ====================================================== */

  const form          = document.getElementById('calc-form');
  const ageInput      = document.getElementById('age');
  const tailleInput   = document.getElementById('taille');
  const poidsInput    = document.getElementById('poids');
  const activiteSel   = document.getElementById('activite');
  const sexeHidden    = document.getElementById('sexe');
  const sexeButtons   = document.querySelectorAll('.segmented-btn');

  const resKcal       = document.getElementById('res-kcal');
  const resText       = document.getElementById('res-text');

  const goalBtns      = document.querySelectorAll('.goal-btn');
  const goalMaintien  = document.getElementById('goal-maintien');
  const goalPerte     = document.getElementById('goal-perte');
  const goalPrise     = document.getElementById('goal-prise');

  const saveBtn       = document.getElementById('save-goal');
  const saveMsg       = document.getElementById('save-goal-msg');

  let maintenanceKcal = null;


  /* ======================================================
     Gestion du switch Homme / Femme
  ====================================================== */

  sexeButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      sexeButtons.forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');
      sexeHidden.value = btn.dataset.sexe;
    });
  });


  /* ======================================================
     Soumission du formulaire
  ====================================================== */

  form?.addEventListener('submit', (e) => {
    e.preventDefault();

    const age      = Number(ageInput.value);
    const taille   = Number(tailleInput.value);
    const poids    = Number(poidsInput.value);
    const activite = Number(activiteSel.value);
    const sexe     = sexeHidden.value || 'H';

    if (!validateInputs(age, taille, poids, activite)) {
      resText.textContent = "Complète toutes les informations avant de lancer le calcul.";
      return;
    }

    const bmr = calculateBMR(sexe, poids, taille, age);
    maintenanceKcal = Math.round(bmr * activite);

    resKcal.textContent = maintenanceKcal;
    resText.textContent = "Estimation de tes besoins journaliers pour maintenir ton poids.";

    updateGoals();
    saveLocalCalculation(age, taille, poids, activite, sexe);
  });


  /* ======================================================
     Fonctions Métabolisme
  ====================================================== */

  function calculateBMR(sexe, poids, taille, age) {
    // Formule Mifflin-St Jeor
    if (sexe === 'H') {
      return 10 * poids + 6.25 * taille - 5 * age + 5;
    }
    return 10 * poids + 6.25 * taille - 5 * age - 161;
  }

  function validateInputs(age, taille, poids, activite) {
    return age > 0 && taille > 0 && poids > 0 && activite > 0;
  }


  /* ======================================================
     Mise à jour des objectifs
  ====================================================== */

  function updateGoals() {
    if (!maintenanceKcal) {
      goalMaintien.textContent = "–";
      goalPerte.textContent    = "–";
      goalPrise.textContent    = "–";
      return;
    }

    goalMaintien.textContent = `${maintenanceKcal} kcal / jour`;
    goalPerte.textContent    = `${Math.max(maintenanceKcal - 400, 0)} kcal / jour`;
    goalPrise.textContent    = `${maintenanceKcal + 300} kcal / jour`;
  }


  /* ======================================================
     Sélection d’un objectif
  ====================================================== */

  goalBtns.forEach(btn => {
    btn.addEventListener('click', () => {

      if (!maintenanceKcal) return;

      goalBtns.forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');

      const name  = btn.dataset.name || "";
      const delta = Number(btn.dataset.delta || 0);
      const target = maintenanceKcal + delta;

      displayActiveGoal(btn, target);
      localStorage.setItem('naha_goal', JSON.stringify({ name, delta }));
    });
  });

  function displayActiveGoal(activeBtn, target) {
    goalBtns.forEach(b => {
      const txt = b.querySelector('.goal-active-text');
      if (txt) txt.textContent = '';
    });

    const activeText = activeBtn.querySelector('.goal-active-text');
    if (activeText) {
      activeText.textContent = `Objectif actuel — ≈ ${target} kcal / jour`;
    }
  }


  /* ======================================================
     Sauvegarde locale
  ====================================================== */

  function saveLocalCalculation(age, taille, poids, activite, sexe) {
    const data = { age, taille, poids, activite, sexe, maintenanceKcal };
    localStorage.setItem('naha_calc', JSON.stringify(data));
  }


  /* ======================================================
     Restauration depuis localStorage
  ====================================================== */

  restoreLocalData();

  function restoreLocalData() {

    const saved = localStorage.getItem('naha_calc');
    if (!saved) return;

    try {
      const data = JSON.parse(saved);

      ageInput.value      = data.age || '';
      tailleInput.value   = data.taille || '';
      poidsInput.value    = data.poids || '';
      activiteSel.value   = data.activite || '';
      sexeHidden.value    = data.sexe || 'H';

      sexeButtons.forEach(b => {
        b.classList.toggle('is-active', b.dataset.sexe === sexeHidden.value);
      });

      if (data.maintenanceKcal) {
        maintenanceKcal = data.maintenanceKcal;
        resKcal.textContent = maintenanceKcal;
        resText.textContent = "Valeur restaurée depuis ta dernière visite.";
        updateGoals();
      }

    } catch (err) {
      console.error("Erreur restauration calcul NAHA :", err);
    }
  }


  /* ======================================================
     Enregistrement serveur
  ====================================================== */

  saveBtn?.addEventListener('click', async () => {

    const goal = getCurrentGoal();
    if (!maintenanceKcal || !goal) {
      saveMsg.textContent = "Calcule d'abord tes besoins et sélectionne un objectif.";
      return;
    }

    saveMsg.textContent = "Enregistrement en cours...";

    const payload = {
      age: Number(ageInput.value),
      taille: Number(tailleInput.value),
      poids: Number(poidsInput.value),
      activite: Number(activiteSel.value),
      sexe: sexeHidden.value || 'H',
      maintenance: maintenanceKcal,
      objectif_nom: goal.name,
      objectif_kcal: goal.target
    };

    try {
      const response = await fetch('save_goal.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const json = await response.json();

      if (!response.ok || !json.ok) {
        saveMsg.textContent = json.message || "Erreur lors de l'enregistrement.";
      } else {
        saveMsg.textContent = "Objectif enregistré avec succès ✅";
      }

    } catch (error) {
      console.error("Erreur réseau :", error);
      saveMsg.textContent = "Erreur réseau. Réessaie plus tard.";
    }

  });


  function getCurrentGoal() {
    const activeBtn = document.querySelector('.goal-btn.is-active');
    if (!activeBtn || !maintenanceKcal) return null;

    const name  = activeBtn.dataset.name || '';
    const delta = Number(activeBtn.dataset.delta || 0);
    return { name, target: maintenanceKcal + delta };
  }

});