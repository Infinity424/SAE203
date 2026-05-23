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
    <?php parametrespage("Accueil"); ?>
</head>
<body style="background-color:#0F1E38;">
    <?php navigation("accueil", "."); ?>
    <section class="container mt-4">
        <div class="text-center text-white py-5">
            <h1 class="display-5 fw-bold" style="color:#1D9E75;">Bienvenue sur TechLoc</h1>
            <p class="lead mt-3">Intranet de l'entreprise – Groupe 3</p>
            <?php if (isset($_SESSION['utilisateur'])): ?>
                <p class="mt-2">Connecté en tant que <strong><?php echo htmlspecialchars($_SESSION['utilisateur']); ?></strong>
                   <span class="badge bg-secondary"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                </p>
            <?php endif; ?>
            <p class="mt-4 text-green small"><?php echo $nbUtilisateurs; ?> membre(s) enregistré(s)</p>
        </div>
    </section>
    <?php piedpage(); ?>
</body>
</html>