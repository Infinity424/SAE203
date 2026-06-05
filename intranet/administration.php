<?php
session_start();
require_once("include/fonctions.php");

// Accès réservé aux admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ./accueil.php");
    exit();
}

$fichier = './data/SAE203-utilisateurs.json';
$message = "";
$typeMsg = "info";

// Lecture du fichier
if (!file_exists($fichier)) {
    die("Erreur : fichier utilisateurs introuvable.");
}
$users = json_decode(file_get_contents($fichier), true);
if (!is_array($users)) {
    die("Erreur : données utilisateurs corrompues.");
}

$roles_valides = ['salarié', 'finance', 'admin', 'com', 'modo', 'manager'];

// Modification du rôle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier_role') {
    $cible       = trim($_POST['utilisateur'] ?? '');
    $nouveau_role = trim($_POST['role'] ?? '');

    if ($cible === '') {
        $message = "Utilisateur cible manquant.";
        $typeMsg = "danger";
    } elseif (!in_array($nouveau_role, $roles_valides)) {
        $message = "Rôle invalide.";
        $typeMsg = "danger";
    } elseif ($cible === 'admin' && $nouveau_role !== 'admin') {
        // Empêcher de retirer le rôle admin au compte admin principal
        $message = "Impossible de modifier le rôle de l'administrateur principal.";
        $typeMsg = "danger";
    } else {
        $trouve = false;
        foreach ($users as &$u) {
            // strcasecmp renvoie 0 si les chaînes sont identiques, sans se soucier des majuscules/minuscules
            if (strcasecmp($u['utilisateur'], $cible) === 0) {
                $u['role'] = $nouveau_role;
                $trouve = true;
                break;
            }
        }
        unset($u);

        if (!$trouve) {
            $message = "Utilisateur introuvable.";
            $typeMsg = "danger";
        } else {
            $resultat = file_put_contents($fichier, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            if ($resultat === false) {
                $message = "Erreur lors de la sauvegarde.";
                $typeMsg = "danger";
            } else {
                $message = "Rôle de « $cible » mis à jour : $nouveau_role.";
                $typeMsg = "success";
            }
        }
    }
}

// Suppression d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $cible = trim($_POST['utilisateur'] ?? '');

    if ($cible === '') {
        $message = "Utilisateur cible manquant.";
        $typeMsg = "danger";
    } elseif ($cible === 'admin') {
        $message = "Impossible de supprimer l'administrateur principal.";
        $typeMsg = "danger";
    } elseif ($cible === $_SESSION['utilisateur']) {
        $message = "Vous ne pouvez pas supprimer votre propre compte.";
        $typeMsg = "danger";
    } else {
        $nb_avant = count($users);
        $users    = array_values(array_filter($users, fn($u) => $u['utilisateur'] !== $cible));

        if (count($users) === $nb_avant) {
            $message = "Utilisateur introuvable.";
            $typeMsg = "warning";
        } else {
            $resultat = file_put_contents($fichier, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            if ($resultat === false) {
                $message = "Erreur lors de la sauvegarde.";
                $typeMsg = "danger";
            } else {
                $message = "Utilisateur « $cible » supprimé.";
                $typeMsg = "success";
            }
        }
    }
}

// Recharger après modification
$users = json_decode(file_get_contents($fichier), true);

// Recherche par nom 
$recherche = trim($_GET['recherche'] ?? '');
$usersFiltres = $users;
if ($recherche !== '') {
    $usersFiltres = array_filter($users, fn($u) => stripos($u['utilisateur'], $recherche) !== false || stripos($u['email'] ?? '', $recherche) !== false);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Administration"); ?>
</head>
<body style="background-color:#0F1E38;">
    <?php navigation("administration", "."); ?>
    <section class="container mt-4">
        <h2 class="mb-3" style="color:#1D9E75;">Administration – Gestion des membres</h2>
        <p class="text" style="color:#1D9E75;"> <?php echo count($users); ?> utilisateur(s) au total.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $typeMsg; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Recherche -->
        <form method="GET" action="administration.php" class="mb-3 d-flex gap-2">
            <input type="text" class="form-control w-auto" name="recherche" placeholder="Rechercher par nom ou email..." value="<?php echo htmlspecialchars($recherche); ?>">
            <button class="btn" style="background-color: #1D9E75;" type="submit">Rechercher</button>
            <?php if ($recherche): ?>
                <a href="administration.php" class="btn btn-outline-secondary">Réinitialiser</a>
            <?php endif; ?>
        </form>

        <?php if (empty($usersFiltres)): ?>
            <div class="alert alert-warning">Aucun utilisateur trouvé.</div>
        <?php else: ?>
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
            <?php foreach ($usersFiltres as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['utilisateur']); ?></td>
                    <td><?php echo htmlspecialchars($u['email'] ?? '—'); ?></td>
                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($u['role']); ?></span></td>
                    <td>
                        <?php if ($u['utilisateur'] !== 'admin'): ?>
                        <form method="POST" action="administration.php" class="d-flex gap-1">
                            <input type="hidden" name="action" value="modifier_role">
                            <input type="hidden" name="utilisateur" value="<?php echo htmlspecialchars($u['utilisateur']); ?>">
                            <select name="role" class="form-select form-select-sm">
                                <?php foreach ($roles_valides as $r): ?>
                                    <option value="<?php echo $r; ?>" <?php if ($u['role'] === $r) echo 'selected'; ?>>
                                        <?php echo $r; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary">OK</button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['utilisateur'] !== 'admin' && $u['utilisateur'] !== $_SESSION['utilisateur']): ?>
                        <form method="POST" action="administration.php"
                              onsubmit="return confirm('Supprimer « <?php echo htmlspecialchars($u['utilisateur'], ENT_QUOTES); ?> » ?')">
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
        <?php endif; ?>
    </section>
    <?php piedpage(); ?>
</body>
</html>