<?php
session_start();
require_once("../fonctions.php");

// Accès réservé aux admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../accueil.php");
    exit();
}

$annonces = json_decode(file_get_contents("../data/r209-tp_annonces.json"), true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("View Annonces"); ?>
</head>
<body>
    <?php
        entete("..");
        navigation("view2", "..");
    ?>
    <section class="container mt-4">
        <h2>Liste des annonces (données brutes)</h2>
        <pre class="bg-light p-3 border rounded"><?php print_r($annonces); ?></pre>
    </section>
    <?php piedpage(); ?>
</body>
</html>