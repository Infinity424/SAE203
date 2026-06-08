<?php
session_start();
require_once("include/fonctions.php");

// Accès réservé aux utilisateurs connectés
if (!estConnecte()) {
    header("Location: ./connexion.php");
    exit();
}

$fichier = './data/SAE203-utilisateurs.json';
$message = "";
$typeMsg = "info";
$pseudo  = $_SESSION['utilisateur'];

// Lecture du fichier
if (!file_exists($fichier)) {
    die("Erreur : fichier utilisateurs introuvable.");
}
$users = json_decode(file_get_contents($fichier), true);
if (!is_array($users)) {
    die("Erreur : données utilisateurs corrompues.");
}

// Trouver l'index de l'utilisateur courant
$userIndex = null;
foreach ($users as $i => $u) {
    if ($u['utilisateur'] === $pseudo) {
        $userIndex = $i;
        break;
    }
}

if ($userIndex === null) {
    session_destroy();
    header("Location: ./connexion.php?erreur=session");
    exit();
}

// Traitement du formulaire de changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'changer_mdp') {
    $mdpActuel  = $_POST['mdp_actuel']  ?? '';
    $mdpNouveau = $_POST['mdp_nouveau'] ?? '';
    $mdpConfirm = $_POST['mdp_confirm'] ?? '';

    if ($mdpActuel === '' || $mdpNouveau === '' || $mdpConfirm === '') {
        $message = "Tous les champs sont obligatoires.";
        $typeMsg = "danger";
    } elseif (!password_verify($mdpActuel, $users[$userIndex]['motdepasse'])) {
        $message = "Le mot de passe actuel est incorrect.";
        $typeMsg = "danger";
    } elseif (strlen($mdpNouveau) < 6) {
        $message = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
        $typeMsg = "danger";
    } elseif ($mdpNouveau !== $mdpConfirm) {
        $message = "Les nouveaux mots de passe ne correspondent pas.";
        $typeMsg = "danger";
    } else {
        $users[$userIndex]['motdepasse'] = password_hash($mdpNouveau, PASSWORD_DEFAULT);
        $resultat = file_put_contents($fichier, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($resultat === false) {
            $message = "Erreur lors de la sauvegarde.";
            $typeMsg = "danger";
        } else {
            $message = "Mot de passe modifié avec succès.";
            $typeMsg = "success";
        }
    }
}

$userActuel = $users[$userIndex];

// Normalisation des rôles pour l'affichage
$rolesAffichage = $userActuel['role'] ?? ['salarié'];
if (is_string($rolesAffichage)) {
    $rolesAffichage = array_filter(array_map('trim', explode(',', $rolesAffichage)));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Mon profil"); ?>
</head>
<body class="d-flex flex-column min-vh-100" style="background-color:#0F1E38;">
    <?php navigation("profil", "."); ?>
    <section class="container mt-4">
        <h2 class="mb-3" style="color:#1D9E75;">Mon profil – <?php echo htmlspecialchars($pseudo); ?></h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $typeMsg; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Infos -->
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Informations</h5>
                        <p><strong>Nom :</strong> <?php echo htmlspecialchars($userActuel['utilisateur']); ?></p>
                        <p><strong>Email :</strong> <?php echo htmlspecialchars($userActuel['email'] ?? '—'); ?></p>
                        <p>
                            <strong>Rôle(s) :</strong>
                            <?php foreach ($rolesAffichage as $r): ?>
                                <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($r); ?></span>
                            <?php endforeach; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Changement de mot de passe -->
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Changer le mot de passe</h5>
                        <form method="POST" action="profil.php">
                            <input type="hidden" name="action" value="changer_mdp">
                            <div class="mb-3">
                                <label for="mdp_actuel" class="form-label">Mot de passe actuel</label>
                                <input type="password" class="form-control" id="mdp_actuel" name="mdp_actuel" required>
                            </div>
                            <div class="mb-3">
                                <label for="mdp_nouveau" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="mdp_nouveau" name="mdp_nouveau"
                                       minlength="6" placeholder="6 caractères minimum" required>
                            </div>
                            <div class="mb-3">
                                <label for="mdp_confirm" class="form-label">Confirmer le nouveau mot de passe</label>
                                <input type="password" class="form-control" id="mdp_confirm" name="mdp_confirm"
                                       minlength="6" required>
                            </div>
                            <button type="submit" class="btn btn-dark">Modifier le mot de passe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php piedpage(); ?>
</body>
</html>