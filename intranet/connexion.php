<?php
session_start();
require_once("include/fonctions.php");

// Si déjà connecté, rediriger vers accueil
if (isset($_SESSION['utilisateur'])) {
    header("Location: ./accueil.php");
    exit();
}

// Récupération du message d'erreur transmis par connexion_traitement.php
$erreurs = [
    'identifiants'  => "Nom d'utilisateur ou mot de passe incorrect.",
    'champs_vides'  => "Veuillez remplir tous les champs.",
    'serveur'       => "Erreur serveur, veuillez réessayer plus tard.",
];
$codeErreur = $_GET['erreur'] ?? '';
$messageErreur = isset($erreurs[$codeErreur]) ? $erreurs[$codeErreur] : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Connexion"); ?>
</head>
<body style="background-color:#0F1E38;">
    <?php navigation("connexion", "."); ?>
    <section class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <h2 class="text-center mb-4" style="color:#1D9E75;" >Connexion au site</h2>
                <h5 class="text-center  mb-4" style="color:#1D9E75;">Entrez votre nom d'utilisateur et votre mot de passe</h5>

                <?php if ($messageErreur): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($messageErreur); ?></div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="connexion_traitement.php" method="POST">
                            <div class="mb-3">
                                <label for="utilisateur" class="form-label">Utilisateur</label>
                                <input type="text" class="form-control" id="utilisateur" name="utilisateur"
                                       placeholder="Nom d'utilisateur"
                                       value="<?php echo htmlspecialchars($_GET['utilisateur'] ?? ''); ?>"
                                       required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="motdepasse" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="motdepasse" name="motdepasse"
                                       placeholder="Mot de passe" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark">Se connecter</button>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php piedpage(); ?>
</body>
</html>