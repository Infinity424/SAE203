<?php
session_start();
require_once("include/fonctions.php");

// Accès réservé aux admins
if (!hasRole('admin')) {
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

// Modification des rôles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier_role') {
    $cible         = trim($_POST['utilisateur'] ?? '');
    // Le select multiple envoie un tableau ; s'il est vide (aucun coché), on force salarié
    $nouveaux_roles = $_POST['roles'] ?? [];

    if ($cible === '') {
        $message = "Utilisateur cible manquant.";
        $typeMsg = "danger";
    } elseif ($cible === 'admin') {
        $message = "Impossible de modifier les rôles de l'administrateur principal.";
        $typeMsg = "danger";
    } else {
        // Nettoyer et valider chaque rôle reçu
        $nouveaux_roles = array_values(array_unique(array_filter(
            array_map('trim', (array)$nouveaux_roles),
            fn($r) => in_array($r, $roles_valides, true)
        )));

        if (empty($nouveaux_roles)) {
            $nouveaux_roles = ['salarié'];
        }

        $trouve = false;
        foreach ($users as &$u) {
            if (strcasecmp($u['utilisateur'], $cible) === 0) {
                $u['role'] = $nouveaux_roles;
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
                $message = "Rôles de « $cible » mis à jour : " . implode(', ', $nouveaux_roles) . ".";
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

// Recherche
$recherche    = trim($_GET['recherche'] ?? '');
$usersFiltres = $users;
if ($recherche !== '') {
    $usersFiltres = array_filter($users, fn($u) =>
        stripos($u['utilisateur'], $recherche) !== false ||
        stripos($u['email'] ?? '', $recherche) !== false
    );
}


//Normalise le champ "role" d'un utilisateur JSON en tableau propre.
function normaliserRoles($role): array {
    if (is_array($role)) {
        return array_values(array_filter(array_map('trim', $role)));
    }
    return array_values(array_filter(array_map('trim', explode(',', (string)$role))));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Administration"); ?>
    <style>
        /* Agrandir un peu le select multiple pour la lisibilité */
        select[multiple] { min-height: 120px; }
    </style>
</head>
<body style="background-color:#0F1E38;">
    <?php navigation("administration", "."); ?>
    <section class="container mt-4">
        <h2 class="mb-3" style="color:#1D9E75;">Administration – Gestion des membres</h2>
        <p style="color:#1D9E75;"><?php echo count($users); ?> utilisateur(s) au total.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $typeMsg; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Recherche -->
        <form method="GET" action="administration.php" class="mb-3 d-flex gap-2">
            <input type="text" class="form-control w-auto" name="recherche"
                   placeholder="Rechercher par nom ou email..."
                   value="<?php echo htmlspecialchars($recherche); ?>">
            <button class="btn" style="background-color:#1D9E75;" type="submit">Rechercher</button>
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
                    <th>Rôles actuels</th>
                    <th>Modifier les rôles</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($usersFiltres as $u):
                $rolesUtilisateur = normaliserRoles($u['role'] ?? 'salarié');
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['utilisateur']); ?></td>
                    <td><?php echo htmlspecialchars($u['email'] ?? '—'); ?></td>
                    <td>
                        <?php foreach ($rolesUtilisateur as $r): ?>
                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($r); ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <?php if ($u['utilisateur'] !== 'admin'): ?>
                        <form method="POST" action="administration.php" class="d-flex gap-1 align-items-start">
                            <input type="hidden" name="action" value="modifier_role">
                            <input type="hidden" name="utilisateur" value="<?php echo htmlspecialchars($u['utilisateur']); ?>">
                            <div>
                                <select name="roles[]" class="form-select form-select-sm" multiple
                                        title="Ctrl+clic pour sélectionner plusieurs rôles">
                                    <?php foreach ($roles_valides as $r): ?>
                                        <option value="<?php echo $r; ?>"
                                            <?php if (in_array($r, $rolesUtilisateur, true)) echo 'selected'; ?>>
                                            <?php echo $r; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted" style="font-size:0.7rem;">Ctrl+clic = multi-sélection</small>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary mt-1">OK</button>
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