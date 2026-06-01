<?php
session_start();
require_once("fonctions.php");

$utilisateurs = count(json_decode(file_get_contents("./data/SAE203-utilisateurs.json"), true));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Accueil"); ?>
</head>
<body>
    <?php
        entete(".");
        navigation("accueil", ".");
    ?>
    
    <?php piedpage(); ?>
</body>
</html>