<?php
session_start();
require_once("fonctions.php");

// Accès réservé aux admins 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ./accueil.php");
    exit();
}

$fichier = './data/SAE203-utilisateurs.json';
$users   = json_decode(file_get_contents($fichier), true);
$message = "";

// Modification du rôle
if (isset($_POST['action']) && $_POST['action'] === 'modifier_role') {
    $cible = $_POST['utilisateur'] ?? '';
    $nouveau_role = $_POST['role'] ?? '';
    $roles_valides = ['salarié','finance','admin','communication'];
    if (in_array($nouveau_role, $roles_valides)) {
        foreach ($users as &$u) {
            if ($u['utilisateur'] === $cible) {
                $u['role'] = $nouveau_role;
                break;
            }
        }
        unset($u);
        file_put_contents($fichier, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = "Rôle de $cible mis à jour : $nouveau_role";
    }
}

// Suppression d'un utilisateur
if (isset($_POST['action']) && $_POST['action'] === 'supprimer') {
    $cible = $_POST['utilisateur'] ?? '';
    if ($cible !== 'admin') { // On ne peut pas supprimer l'admin
        $users = array_filter($users, fn($u) => $u['utilisateur'] !== $cible);
        $users = array_values($users);
        file_put_contents($fichier, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $message = "Utilisateur $cible supprimé.";
    } else {
        $message = "Impossible de supprimer l'administrateur principal.";
    }
}

// Recharger après modification
$users = json_decode(file_get_contents($fichier), true);

// Recherche par nom
$recherche = trim($_GET['recherche'] ?? '');
if ($recherche !== '') {
    $users = array_filter($users, fn($u) => stripos($u['utilisateur'], $recherche) !== false);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Administration"); ?>
</head>
<body>
    <?php
        navigation("administration", ".");
    ?>
    <section class="container mt-4">
        <h2 class="mb-3">Administration – Gestion des membres de l'entreprise</h2>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Recherche -->
        <form method="GET" action="administration.php" class="mb-3 d-flex gap-2">
            <input type="text" class="form-control w-auto" name="recherche"
                   placeholder="Rechercher un utilisateur..."
                   value="<?php echo htmlspecialchars($recherche); ?>">
            <button class="btn btn-outline-dark" type="submit">Rechercher</button>
            <?php if ($recherche): ?>
                <a href="administration.php" class="btn btn-outline-secondary">Réinitialiser</a>
            <?php endif; ?>
        </form>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle actuel</th>
                    <th>Changer le rôle</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['utilisateur']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($u['role']); ?></span></td>
                    <td>
                        <form method="POST" action="administration.php" class="d-flex gap-1">
                            <input type="hidden" name="action" value="modifier_role">
                            <input type="hidden" name="utilisateur" value="<?php echo htmlspecialchars($u['utilisateur']); ?>">
                            <select name="role" class="form-select form-select-sm">
                                <?php foreach (['salarié','finance','admin','communication'] as $r): ?>
                                    <option value="<?php echo $r; ?>" <?php if ($u['role'] === $r) echo 'selected'; ?>>
                                        <?php echo $r; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary">OK</button>
                        </form>
                    </td>
                    <td>
                        <?php if ($u['utilisateur'] !== 'admin'): ?>
                        <form method="POST" action="administration.php"
                              onsubmit="return confirm('Supprimer <?php echo htmlspecialchars($u['utilisateur']); ?> ?')">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="utilisateur" value="<?php echo htmlspecialchars($u['utilisateur']); ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php piedpage(); ?>
</body>
</html>