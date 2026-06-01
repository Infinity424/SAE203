<?php
session_start();
require_once("fonctions.php");

// Accès réservé aux admins uniquement
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ./accueil.php");
    exit();
}

$erreur = "";
$succes = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['utilisateur'] ?? '');
    $email  = trim($_POST['email']       ?? '');
    $mdp    = $_POST['motdepasse']       ?? '';
    $mdp2   = $_POST['motdepasse2']      ?? '';

    // Validation
    if ($pseudo === '' || $email === '' || $mdp === '') {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "L'adresse email n'est pas valide.";
    } elseif (strlen($pseudo) < 3 || strlen($pseudo) > 30) {
        $erreur = "Le nom d'utilisateur doit contenir entre 3 et 30 caractères.";
    } elseif (!preg_match('/^[a-zA-Z0-9_\-\.éèêëàâùûüîïôç ]+$/u', $pseudo)) {
        $erreur = "Le nom d'utilisateur contient des caractères non autorisés.";
    } elseif ($mdp !== $mdp2) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($mdp) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $fichier = './data/SAE203-utilisateurs.json';
        if (!file_exists($fichier)) {
            $erreur = "Erreur serveur : fichier utilisateurs introuvable.";
        } else {
            $contenu = file_get_contents($fichier);
            $users   = json_decode($contenu, true);

            if (!is_array($users)) {
                $erreur = "Erreur serveur : données corrompues.";
            } else {
                // Vérifier pseudo et email uniques (insensible à la casse pour l'email)
                $pseudoExiste = false;
                $emailExiste  = false;
                foreach ($users as $u) {
                    if ($u['utilisateur'] === $pseudo) {
                        $pseudoExiste = true;
                    }
                    if (isset($u['email']) && strtolower($u['email']) === strtolower($email)) {
                        $emailExiste = true;
                    }
                }

                if ($pseudoExiste) {
                    $erreur = "Ce nom d'utilisateur est déjà pris.";
                } elseif ($emailExiste) {
                    $erreur = "Cette adresse email est déjà utilisée.";
                } else {
                    $nouvelUser = [
                        "utilisateur" => $pseudo,
                        "motdepasse"  => password_hash($mdp, PASSWORD_DEFAULT),
                        "email"       => $email,
                        "role"        => "salarié"
                    ];
                    $users[] = $nouvelUser;

                    $resultat = file_put_contents(
                        $fichier,
                        json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    );

                    if ($resultat === false) {
                        $erreur = "Erreur lors de l'enregistrement du compte.";
                    } else {
                        $succes = "Compte créé avec succès ! Vous pouvez vous connecter.";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Inscription"); ?>
</head>
<body style="background-color:#0F1E38;">
    <?php navigation("inscription", "."); ?>
    <section class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4" style="color:#1D9E75;">Créer un compte</h2>

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
                                <label for="utilisateur" class="form-label">Nom d'utilisateur</label>
                                <input type="text" class="form-control" id="utilisateur" name="utilisateur"
                                       value="<?php echo htmlspecialchars($_POST['utilisateur'] ?? ''); ?>"
                                       minlength="3" maxlength="30" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
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