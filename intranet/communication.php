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
    <?php parametrespage("Mon profil"); ?>
</head>
<body style="background-color:#0F1E38;">
    <?php navigation("profil", "."); ?>
    <section class="container mt-4" >
        <h2 class="text-center mb-4" style="color:#1D9E75;">Travaillons ensemble</h2>
                <form method="POST" action="profil.php">
            <div class="row">
                <!-- Infos -->
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="utilisateur" class="form-label">Nom d'utilisateur</label>
                                <input type="text" class="form-control" id="utilisateur" name="utilisateur">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="mb-3">
                                <label for="motdepasse" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="motdepasse" name="motdepasse"
                                    placeholder="6 caractères minimum" minlength="6" required>
                            </div>
                            <div class="mb-3">
                                <label for="motdepasse2" class="form-label">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" id="motdepasse2" name="motdepasse2"
                                    minlength="6" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message -->
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Message</h5>
                            <div class="mb-3">
                                <textarea class="form-control" rows="5" id="comment" name="text"></textarea>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-dark">Envoyer</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
    <?php piedpage(); ?>
</body>
</html>