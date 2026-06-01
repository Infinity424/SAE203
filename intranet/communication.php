<?php
session_start();
require_once("fonctions.php");

// Lecture du fichier pour le compteur d'utilisateur
$nbUtilisateurs = 0;
$fichier = "./data/SAE203-utilisateurs.json";
if (file_exists($fichier)) {
    $data = json_decode(file_get_contents($fichier), true);
    if (is_array($data)) {
        $nbUtilisateurs = count($data);
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Communication"); ?>
</head>
<body style="background-color:#0F1E38;">
    <?php navigation("communication", "."); ?>
    <section class="container mt-4">
        
    </section>
    <?php piedpage(); ?>
</body>
</html>