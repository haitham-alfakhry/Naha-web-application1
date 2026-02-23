<?php
/**
 * ============================================================
 * NAHA - Page Profil Utilisateur
 * Fichier : profil.php
 * Description :
 *  - Affiche les informations personnelles
 *  - Calcul IMC automatique
 *  - Historique du poids (graphique Chart.js)
 *  - Modification via modal sécurisé
 *
 * Author : HAITHAM ALFAKHRY
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "bdd.php";
$bdd = getBD();

/* ============================================================
 * 1️⃣ Vérification connexion utilisateur
 * ============================================================ */
if (!isset($_SESSION['utilisateur'])) {
    header("Location: seconnecter.php");
    exit;
}

$id = $_SESSION['utilisateur']['id_utilisateur'];

/* ============================================================
 * 2️⃣ Récupération données utilisateur
 * ============================================================ */
$req = $bdd->prepare("SELECT * FROM utilisateurs WHERE id_utilisateur=?");
$req->execute([$id]);
$user = $req->fetch(PDO::FETCH_ASSOC);

/* ============================================================
 * 3️⃣ Récupération dernier objectif
 * ============================================================ */
$req2 = $bdd->prepare("
    SELECT * FROM objectif_utilisateur 
    WHERE id_utilisateur=? 
    ORDER BY date_maj DESC 
    LIMIT 1
");
$req2->execute([$id]);
$goal = $req2->fetch(PDO::FETCH_ASSOC);

/* Valeurs par défaut */
$age    = $goal['age']    ?? "Non défini";
$poids  = $goal['poids']  ?? 0;
$taille = $goal['taille'] ?? 0;

/* ============================================================
 * 4️⃣ Calcul IMC
 * ============================================================ */
$imc = 0;
if ($poids > 0 && $taille > 0) {
    $imc = round($poids / (($taille / 100) ** 2), 1);
}

/* ============================================================
 * 5️⃣ Historique poids
 * ============================================================ */
$req3 = $bdd->prepare("
    SELECT poids, date_maj 
    FROM objectif_utilisateur 
    WHERE id_utilisateur=? 
    ORDER BY date_maj ASC
");
$req3->execute([$id]);
$poidsData = $req3->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon Profil — NAHA</title>

<link rel="stylesheet" href="accueil-style.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700;800&display=swap" rel="stylesheet">
</head>

<body>

<?php include "header.php"; ?>

<main class="container" style="padding:40px 0;">

<h1 style="text-align:center; font-weight:800; margin-bottom:30px;">Mon Profil</h1>

<div style="display:flex; gap:2rem; flex-wrap:wrap; justify-content:center;">

<!-- ================= AVATAR ================= -->
<div style="background:white;padding:2rem;border-radius:20px;width:350px;text-align:center;box-shadow:0 8px 18px rgba(0,0,0,0.1);">

<img src="<?= htmlspecialchars($user['avatar'] ?? 'assets/img/default-avatar.jpg') ?>"
     style="width:160px;height:160px;object-fit:cover;border-radius:15px;margin-bottom:15px;box-shadow:0 6px 15px rgba(0,0,0,0.15);">

<h3 style="margin:10px 0 0; font-weight:700;">
<?= htmlspecialchars($user['prenom']." ".$user['nom']) ?>
</h3>

<p style="margin-top:6px; color:#16a34a; font-weight:600;">● Connecté</p>

<button class="btn-purple"
onclick="document.getElementById('modal').style.display='flex'">
Modifier
</button>
</div>

<!-- ================= INFOS ================= -->
<div style="background:white;padding:2rem;border-radius:20px;width:350px;box-shadow:0 8px 18px rgba(0,0,0,0.1);">
<h3>Informations personnelles</h3><br>

<p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
<p><strong>Prénom :</strong> <?= htmlspecialchars($user['prenom']) ?></p>
<p><strong>Âge :</strong> <?= $age ?> ans</p>
<p><strong>Poids :</strong> <?= $poids ?> kg</p>
<p><strong>Taille :</strong> <?= $taille ?> cm</p>
</div>

<!-- ================= IMC ================= -->
<div style="background:white;padding:2rem;border-radius:20px;width:350px;box-shadow:0 8px 18px rgba(0,0,0,0.1);text-align:center;">
<h2>Indice de Masse Corporelle</h2>

<p style="font-size:42px; margin:10px 0; font-weight:800;"><?= $imc ?></p>

<p>
<?php
if ($imc == 0) echo "Non défini";
elseif ($imc < 18.5) echo "Insuffisance pondérale";
elseif ($imc < 25) echo "Poids normal";
elseif ($imc < 30) echo "Surpoids";
else echo "Obésité";
?>
</p>
</div>

</div>

<!-- ================= GRAPH ================= -->
<div style="margin-top:40px;background:white;padding:2rem;border-radius:20px;box-shadow:0 8px 18px rgba(0,0,0,0.1);">
<h2 style="margin-bottom:20px;">Évolution du poids</h2>
<canvas id="poidsChart" height="110"></canvas>
</div>

</main>

<!-- ================= MODAL ================= -->
<div id="modal" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.55);display:none;justify-content:center;align-items:center;">
<div style="background:white;width:380px;padding:2rem;border-radius:20px;box-shadow:0 12px 30px rgba(0,0,0,0.25);">

<h2>Modifier mes informations</h2>

<form action="update_profil.php" method="POST" enctype="multipart/form-data"
style="display:grid;grid-template-columns:1fr 1fr;gap:1rem 1.5rem;margin-top:1rem;">

<div>
<label>Nom</label>
<input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>">
</div>

<div>
<label>Prénom</label>
<input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>">
</div>

<div>
<label>Âge</label>
<input type="number" name="age" value="<?= ($age === 'Non défini' ? '' : $age) ?>">
</div>

<div>
<label>Poids</label>
<input type="number" name="poids" value="<?= $poids ?>">
</div>

<div>
<label>Taille</label>
<input type="number" name="taille" value="<?= $taille ?>">
</div>

<div>
<label>Photo</label>
<input type="file" name="avatar" accept="image/*">
</div>

<div style="grid-column: span 2; text-align:center;">
<button type="submit" class="btn btn-primary">Sauvegarder</button>
</div>

</form>

<button onclick="document.getElementById('modal').style.display='none'"
class="btn" style="margin-top:10px;background:#aaa;color:white;">
Fermer
</button>

</div>
</div>

<!-- ================= SCRIPT CHART ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const poidsData = <?= json_encode(array_column($poidsData,'poids')) ?>;
const dates = <?= json_encode(array_column($poidsData,'date_maj')) ?>;

new Chart(document.getElementById('poidsChart'), {
type: 'line',
data: {
labels: dates,
datasets: [{
label: 'Poids (kg)',
data: poidsData,
tension: 0.3,
borderWidth: 3,
borderColor: '#4f46e5',
backgroundColor: "rgba(99,102,241,0.2)"
}]
}
});
</script>

<?php include "footer.php"; ?>
</body>
</html>