<?php
session_start();
require_once("fonctions.php");

// Accès réservé aux utilisateurs connectés
if (!isset($_SESSION['utilisateur'])) {
    header("Location: ./connexion.php");
    exit();
}

$fichier  = './data/SAE203-utilisateurs.json';
$users    = json_decode(file_get_contents($fichier), true);
$message  = "";
$pseudo   = $_SESSION['utilisateur'];

// Trouver l'utilisateur courant
$userIndex = null;
foreach ($users as $i => $u) {
    if ($u['utilisateur'] === $pseudo) {
        $userIndex = $i;
        break;
    }
}
$userActuel = $users[$userIndex];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Mon profil"); ?>
</head>
<body>
    <?php
        entete(".");
        navigation("profil", ".");
    ?>
    <section class="container mt-4">
        <h2 class="mb-3">Mon profil – <?php echo htmlspecialchars($pseudo); ?></h2>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Infos -->
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Informations</h5>
                        <p><strong>Nom :</strong> <?php echo htmlspecialchars($userActuel['utilisateur']); ?></p>
                        <p><strong>Email :</strong> <?php echo htmlspecialchars($userActuel['email']); ?></p>
                        <p><strong>Rôle :</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($userActuel['role']); ?></span></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php piedpage(); ?>
</body>
</html>