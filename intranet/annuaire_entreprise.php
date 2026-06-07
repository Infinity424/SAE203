<?php
session_start();
require_once("include/fonctions.php");

// Accès réservé aux utilisateurs connectés
if (!isset($_SESSION['utilisateur'])) {
    header("Location: ./connexion.php");
    exit();
}

$fichier = './data/SAE203-utilisateurs.json';
$message = "";
$typeMsg = "info";

// Lecture initiale
if (!file_exists($fichier)) {
    die("Erreur : fichier entreprise introuvable.");
}
$membres = json_decode(file_get_contents($fichier), true);
if (!is_array($membres)) {
    die("Erreur : données entreprise corrompues.");
}

$roles_valides = ['salarié', 'finance', 'admin', 'com', 'modo', 'manager'];

//  Supression 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $cible = trim($_POST['utilisateur'] ?? '');

    if ($cible === '') {
        $message = "Salarié cible manquant.";
        $typeMsg  = "danger";
    } elseif ($cible === 'admin') {
        $message = "Impossible de supprimer l'administrateur principal.";
        $typeMsg  = "danger";
    } elseif ($cible === $_SESSION['utilisateur']) {
        $message = "Vous ne pouvez pas supprimer votre propre compte.";
        $typeMsg  = "danger";
    } else {
        $nb_avant = count($membres);
        $membres  = array_values(array_filter($membres, fn($m) => $m['utilisateur'] !== $cible));

        if (count($membres) === $nb_avant) {
            $message = "Salarié introuvable.";
            $typeMsg  = "warning";
        } else {
            $resultat = file_put_contents($fichier, json_encode($membres, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message  = $resultat !== false ? "Salarié « $cible » supprimé." : "Erreur lors de la sauvegarde.";
            $typeMsg  = $resultat !== false ? "success" : "danger";
        }
    }
}

// Modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {
    $nomOriginal  = trim($_POST['nom_original'] ?? '');
    $nom          = trim($_POST['nom']          ?? '');
    $email        = trim($_POST['email']        ?? '');
    $nouveauxRoles = $_POST['roles'] ?? [];

    if ($nomOriginal === '' || $nom === '' || $email === '') {
        $message = "Nom et email sont obligatoires.";
        $typeMsg  = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "L'adresse email n'est pas valide.";
        $typeMsg  = "danger";
    } elseif ($nomOriginal === 'admin') {
        $message = "Impossible de modifier l'administrateur principal.";
        $typeMsg  = "danger";
    } else {
        // Nettoie et valide les rôles reçus
        $nouveauxRoles = array_values(array_unique(array_filter(
            array_map('trim', (array)$nouveauxRoles),
            fn($r) => in_array($r, $roles_valides, true)
        )));
        if (empty($nouveauxRoles)) {
            $nouveauxRoles = ['salarié'];
        }

        $trouve = false;
        foreach ($membres as &$m) {
            if ($m['utilisateur'] === $nomOriginal) {
                $m['utilisateur'] = $nom;
                $m['email']       = $email;
                $m['role']        = $nouveauxRoles;
                $trouve = true;
                break;
            }
        }
        unset($m);

        if (!$trouve) {
            $message = "Salarié introuvable.";
            $typeMsg  = "warning";
        } else {
            $resultat = file_put_contents($fichier, json_encode($membres, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message  = $resultat !== false ? "Salarié « $nom » modifié." : "Erreur lors de la sauvegarde.";
            $typeMsg  = $resultat !== false ? "success" : "danger";
        }
    }
}

$membres = json_decode(file_get_contents($fichier), true);

$accesParPage = [
    'accueil'              => ['admin', 'manager', 'modo', 'com', 'finance', 'salarié'],
    'profil'               => ['admin', 'manager', 'modo', 'com', 'finance', 'salarié'],
    'inscription'          => ['admin', 'manager'],
    'administration'       => ['admin'],
    'communication'        => ['admin', 'manager', 'modo', 'com'],
    'finance'              => ['admin', 'manager', 'modo', 'finance'],
    'annuaire_clients'     => ['admin', 'manager', 'modo', 'com', 'finance', 'salarié'],
    'annuaire_entreprise'  => ['admin', 'manager', 'modo', 'com', 'finance', 'salarié'],
    'annuaire_partenaires' => ['admin', 'manager', 'modo', 'com', 'finance', 'salarié'],
];

function membreMatcheRecherche(array $rolesUser, string $recherche, array $accesParPage): bool {
    foreach ($accesParPage as $page => $rolesAutorises) {
        if (stripos($page, $recherche) !== false) {
            foreach ($rolesUser as $r) {
                if (in_array($r, $rolesAutorises, true)) return true;
            }
        }
    }
    return false;
}

//export
if (isset($_GET['export'])) {
    if ($_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="annuaire_entreprise.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, ['Nom', 'Service / Rôle', 'Email'], ';');
        foreach ($membres as $m) {
            fputcsv($out, [
                $m['utilisateur'] ?? '',
                is_array($m['role'] ?? '') ? implode(', ', $m['role']) : ($m['role'] ?? ''),
                $m['email'] ?? '',
            ], ';');
        }
        fclose($out);
        exit();
    } elseif ($_GET['export'] === 'txt') {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="annuaire_entreprise.txt"');
        $ligne = str_repeat('-', 60) . "\n";
        echo "ANNUAIRE ENTREPRISE – TechLoc\n";
        echo "Exporté le " . date('d/m/Y à H:i') . "\n";
        echo $ligne;
        foreach ($membres as $m) {
            echo "Nom     : " . ($m['utilisateur'] ?? '—') . "\n";
            echo "Service : " . (is_array($m['role'] ?? '') ? implode(', ', $m['role']) : ($m['role'] ?? '—')) . "\n";
            echo "Email   : " . ($m['email'] ?? '—') . "\n";
            echo $ligne;
        }
        exit();
    }
}

// recherche
$recherche = trim($_GET['recherche'] ?? '');
$membresFiltres = $membres;
if ($recherche !== '') {
    $membresFiltres = array_filter($membres, function($m) use ($recherche, $accesParPage) {
        $r = $m['role'] ?? '';
        $rolesUser = is_array($r) ? $r : array_filter(array_map('trim', explode(',', $r)));
        if (stripos($m['utilisateur'] ?? '', $recherche) !== false) return true;
        if (stripos($m['email']       ?? '', $recherche) !== false) return true;
        foreach ($rolesUser as $role) {
            if (stripos($role, $recherche) !== false) return true;
        }
        return membreMatcheRecherche($rolesUser, $recherche, $accesParPage);
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Annuaire Entreprise"); ?>
</head>
<body class="d-flex flex-column min-vh-100" style="background-color:#0F1E38;">
    <?php navigation("annuaire_entreprise", "."); ?>
    <section class="container mt-4">
        <h2 class="mb-1" style="color:#1D9E75;">Annuaire – Entreprise</h2>
        <p style="color:#1D9E75;"><?php echo count($membres); ?> membre(s) au total.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $typeMsg; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="mb-3 d-flex gap-2">
            <a href="annuaire_entreprise.php?export=csv" class="btn btn-sm fw-bold" style="background-color:#1D9E75; color:white; border:none;">Télécharger CSV</a>
            <a href="annuaire_entreprise.php?export=txt" class="btn btn-sm btn-outline-secondary">Télécharger TXT</a>
        </div>

        <form method="GET" action="annuaire_entreprise.php" class="mb-3 d-flex gap-2">
            <input type="text" class="form-control w-auto" name="recherche"
                   placeholder="Rechercher par nom, service..."
                   value="<?php echo htmlspecialchars($recherche); ?>">
            <button class="btn" style="color:#1D9E75; border-color:#1D9E75;" type="submit">Rechercher</button>
            <?php if ($recherche): ?>
                <a href="annuaire_entreprise.php" class="btn btn-outline-secondary">Réinitialiser</a>
            <?php endif; ?>
        </form>

        <?php if (empty($membresFiltres)): ?>
            <div class="alert alert-info">Aucun membre trouvé.</div>
        <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Nom</th>
                    <th>Service / Rôle(s)</th>
                    <th>Email</th>
                    <?php if (hasRole('admin', 'manager')): ?>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($membresFiltres as $m):
                $r = $m['role'] ?? '—';
                $rolesM = is_array($r) ? $r : array_filter(array_map('trim', explode(',', $r)));
                $rolesStr = implode(',', $rolesM);
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($m['utilisateur'] ?? '—'); ?></td>
                    <td>
                        <?php foreach ($rolesM as $badge): ?>
                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($badge); ?></span>
                        <?php endforeach; ?>
                    </td>
                    <td><?php echo htmlspecialchars($m['email'] ?? '—'); ?></td>

                    <?php if (hasRole('admin', 'manager')): ?>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalModifier"
                            data-nom="<?php echo htmlspecialchars($m['utilisateur'], ENT_QUOTES); ?>"
                            data-email="<?php echo htmlspecialchars($m['email'] ?? '', ENT_QUOTES); ?>"
                            data-roles="<?php echo htmlspecialchars($rolesStr, ENT_QUOTES); ?>">
                            Modifier
                        </button>
                    </td>
                    <td>
                        <?php if ($m['utilisateur'] !== 'admin' && $m['utilisateur'] !== $_SESSION['utilisateur']): ?>
                        <form method="POST" action="annuaire_entreprise.php"
                              onsubmit="return confirm('Supprimer « <?php echo htmlspecialchars($m['utilisateur'], ENT_QUOTES); ?> » ?')">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="utilisateur" value="<?php echo htmlspecialchars($m['utilisateur']); ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

    <!-- Modal pour la modification -->
    <?php if (hasRole('admin', 'manager')): ?>
    <div class="modal fade" id="modalModifier" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier un membre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="annuaire_entreprise.php">
                    <input type="hidden" name="action" value="modifier">
                    <input type="hidden" name="nom_original" id="modal-nom-original">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="nom" id="modal-nom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" id="modal-email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rôle(s)</label>
                            <select name="roles[]" class="form-select" multiple style="min-height:130px;"
                                    title="Ctrl+clic pour sélectionner plusieurs rôles" id="modal-roles">
                                <?php foreach ($roles_valides as $r): ?>
                                    <option value="<?php echo $r; ?>"><?php echo $r; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Ctrl+clic = multi-sélection</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn fw-bold" style="background-color:#1D9E75; color:white; border:none;">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('modalModifier').addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            const nom    = btn.dataset.nom;
            const email  = btn.dataset.email;
            const roles  = btn.dataset.roles ? btn.dataset.roles.split(',') : [];

            document.getElementById('modal-nom-original').value = nom;
            document.getElementById('modal-nom').value          = nom;
            document.getElementById('modal-email').value        = email;

            // Coche les bons rôles dans le select multiple
            const select = document.getElementById('modal-roles');
            for (let option of select.options) {
                option.selected = roles.includes(option.value);
            }
        });
    </script>
    <?php endif; ?>

    <?php piedpage(); ?>
</body>
</html>