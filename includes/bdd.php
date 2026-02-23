<?php
/**
 * ---------------------------------------------------------
 * Fichier : bdd.php (ou connexion_pdo.php)
 * Projet  : NAHA Web Application
 * Rôle    : Fournir une connexion PDO sécurisée
 * ---------------------------------------------------------
 * Cette fonction permet d'établir une connexion à la base
 * de données MySQL en utilisant l'extension PDO.
 * Elle centralise l'accès à la base pour tout le projet.
 * ---------------------------------------------------------
 */

function getBD() {
    try {

        /**
         * Paramètres de connexion
         * - host : serveur local
         * - port : port MySQL par défaut (3306)
         * - dbname : nom de la base de données
         * - charset : encodage UTF-8
         */
        $dsn = "mysql:host=localhost;port=3306;dbname=naha;charset=utf8";

        // Identifiants de connexion
        $user = "root";
        $pass = "";

        /**
         * Création de l'objet PDO
         */
        $bdd = new PDO($dsn, $user, $pass);

        /**
         * Activation du mode d'erreur Exception
         * Permet de capturer proprement les erreurs SQL
         */
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $bdd;

    } catch (PDOException $e) {

        /**
         * En cas d'erreur, arrêt du script avec message explicite
         * (En production, il serait préférable de masquer le message)
         */
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}
?>