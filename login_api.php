<?php
/**
 * ============================================================
 * NAHA - API Login
 * Fichier : login_api.php
 * Description :
 *  - Vérifie les identifiants utilisateur (email + mot de passe)
 *  - Utilise password_verify pour la sécurité
 *  - Initialise la session utilisateur en cas de succès
 *  - Retourne une réponse JSON (succès / erreur)
 *
 * Author : HAITHAM ALFAKHRY
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once "bdd.php";

try {
    $bdd = getBD();

    $mail = trim($_POST['mail'] ?? '');
    $pass = $_POST['pswrd'] ?? '';

    // Vérification champs vides
    if ($mail === '' || $pass === '') {
        echo json_encode([
            "success" => false,
            "msg" => "Email ou mot de passe manquant."
        ]);
        exit;
    }

    // Recherche utilisateur
    $stmt = $bdd->prepare("SELECT * FROM utilisateurs WHERE mail = ?");
    $stmt->execute([$mail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "success" => false,
            "msg" => "Compte introuvable."
        ]);
        exit;
    }

    // Vérification mot de passe sécurisé
    if (!password_verify($pass, $user['pswrd'])) {
        echo json_encode([
            "success" => false,
            "msg" => "Mot de passe incorrect."
        ]);
        exit;
    }

    // Initialisation session
    $_SESSION['utilisateur'] = [
        "id_utilisateur" => $user['id_utilisateur'],
        "nom"            => $user['nom'],
        "prenom"         => $user['prenom'],
        "mail"           => $user['mail']
    ];

    // Réponse succès
    echo json_encode([
        "success"  => true,
        "msg"      => "Connexion réussie !",
        "redirect" => "accueil.php"
    ]);
    exit;

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "msg"     => "Erreur serveur. Réessaie plus tard."
    ]);
    exit;
}