<?php
session_start();
require_once("fonctions.php");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Connexion - Traitement"); ?>
</head>
<body>
    <?php
    
        navigation("connexion", ".");
    ?>
    <section class="container mt-4">
        <?php
            $utilisateur_saisi = $_POST["utilisateur"] ?? '';
            $motdepasse_saisi  = $_POST["motdepasse"]  ?? '';

            $fichier = file_get_contents('./data/SAE203-utilisateurs.json');
            $json    = json_decode($fichier, true);

            $flag = 0;
            $utilisateurConnecte = null;

            foreach ($json as $user) {
                if ($user["utilisateur"] === $utilisateur_saisi) {
                    if (password_verify($motdepasse_saisi, $user["motdepasse"])) {
                        $flag = 1;
                        $utilisateurConnecte = $user;
                    }
                }
            }

            if ($flag === 0) {
                echo '<div class="alert alert-danger">';
                echo 'Erreur : le nom d\'utilisateur ou le mot de passe est incorrect.';
                echo '<br><a href="./connexion.php" class="btn btn-outline-dark mt-2">Retour au formulaire</a>';
                echo '</div>';
            } else {
                // Création des variables de session
                $_SESSION['utilisateur'] = $utilisateurConnecte['utilisateur'];
                $_SESSION['role']        = $utilisateurConnecte['role'];

                echo '<div class="alert alert-success">';
                echo 'Bienvenue <strong>' . htmlspecialchars($_SESSION['utilisateur']) . '</strong> !';
                echo ' Votre rôle est : <strong>' . htmlspecialchars($_SESSION['role']) . '</strong>.';
                echo '<br><a href="./accueil.php" class="btn btn-outline-dark mt-2">Aller à l\'accueil</a>';
                echo '</div>';
            }
            header("Location: ./test.php");
        ?>
    </section>
    <?php piedpage(); ?>
</body>
</html>