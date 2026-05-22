<?php
session_start();
require_once("fonctions.php");
// Si déjà connecté, rediriger vers accueil
if (isset($_SESSION['utilisateur'])) {
    header("Location: ./test.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Connexion"); ?>
</head>
<body>
    <?php
        navigation("connexion", ".");
    ?>
    <section class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <h2 class="text-center mb-4">Connexion au site</h2>
                <h5 class="text-center text-muted mb-4">Entrez votre nom d'utilisateur et votre mot de passe</h5>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="connexion_traitement.php" method="POST">
                            <div class="mb-3">
                                <label for="utilisateur" class="form-label">Utilisateur</label>
                                <input type="text" class="form-control" id="utilisateur" name="utilisateur" placeholder="Nom d'utilisateur" required>
                            </div>
                            <div class="mb-3">
                                <label for="motdepasse" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="motdepasse" name="motdepasse" placeholder="Mot de passe" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark">Se connecter</button>
                            </div>
                        </form>
                        <p class="mt-3 text-muted text-center small">
                            Utilisateur normal : user / motdepasse<br>
                            Utilisateur admin : admin / motdepasse<br>
                        </p>
                        <div class="text-center mt-2">
                            <a href="./inscription.php">Pas encore inscrit ? Créer un compte</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php piedpage(); ?>
</body>
</html>