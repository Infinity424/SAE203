<?php
session_start();
require_once("../scripts/fonctions.php");

// Accès réservé aux admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../accueil.php");
    exit();
}

$utilisateurs = json_decode(file_get_contents("../data/r209-tp_utilisateurs.json"), true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("View Utilisateurs"); ?>
</head>
<body>
    <?php
        entete("..");
        navigation("view1", "..");
    ?>
    <section class="container mt-4">
        <h2>Liste des utilisateurs (données brutes)</h2>
        <pre class="bg-light p-3 border rounded"><?php print_r($utilisateurs); ?></pre>
    </section>
    <?php piedpage(); ?>
</body>
</html>