<?php
session_start();
require_once("fonctions.php");

$erreur  = "";
$succes  = "";

//recupère les element utile a la connection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo   = trim($_POST['utilisateur'] ?? '');
    $email    = trim($_POST['email']       ?? '');
    $mdp      = $_POST['motdepasse']       ?? '';
    $mdp2     = $_POST['motdepasse2']      ?? '';

    if ($pseudo === '' || $email === '' || $mdp === '') {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif ($mdp !== $mdp2) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($mdp) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $fichier = file_get_contents('./data/SAE203-utilisateurs.json');
        $users   = json_decode($fichier, true);

        // Vérifier pseudo unique
        $existe = false;
        foreach ($users as $u) {
            if ($u['utilisateur'] === $pseudo) {
                $existe = true;
                break;
            }
        }

        if ($existe) {
            $erreur = "Ce nom d'utilisateur est déjà pris.";
        } else {
            $nouvelUser = [
                "utilisateur" => $pseudo,
                "motdepasse"  => password_hash($mdp, PASSWORD_DEFAULT),
                "email"       => $email,
                "role"        => "user"
            ];
            $users[] = $nouvelUser;
            file_put_contents('./data/SAE203-utilisateurs.json', json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $succes = "Compte créé avec succès ! Vous pouvez vous connecter.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Inscription"); ?>
</head>
<body>
    <?php
        navigation("inscription", ".");
    ?>
    <section class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Créer un compte</h2>

                <?php if ($erreur): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($erreur); ?></div>
                <?php endif; ?>
                <?php if ($succes): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($succes); ?>
                        <br><a href="./connexion.php" class="btn btn-dark mt-2">Se connecter</a>
                    </div>
                <?php endif; ?>

                <?php if (!$succes): ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="inscription.php">
                            <div class="mb-3">
                                <label for="utilisateur" class="form-label">Prenom (unique)</label>
                                <input type="text" class="form-control" id="utilisateur" name="utilisateur"
                                       value="<?php echo htmlspecialchars($_POST['utilisateur'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="motdepasse" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="motdepasse" name="motdepasse"
                                       placeholder="6 caractères minimum" required>
                            </div>
                            <div class="mb-3">
                                <label for="motdepasse2" class="form-label">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" id="motdepasse2" name="motdepasse2" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark">Créer le compte</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="./connexion.php">Déjà un compte ? Se connecter</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php piedpage(); ?>
</body>
</html>