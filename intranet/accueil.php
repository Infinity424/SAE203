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
<body style="background-color:#0F1E38;">
    <?php
        
        navigation("accueil", ".");
    ?>
    <section>
    </section>
    <?php piedpage(); ?>
</body>
</html>