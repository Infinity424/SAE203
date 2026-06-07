<?php
session_start();
require_once("include/fonctions.php");

// Accès réservé aux utilisateurs connectés
if (!isset($_SESSION['utilisateur'])) {
    header("Location: ./connexion.php");
    exit();
}

$fichier     = './data/annuaire_partenaires.json';
$message     = "";
$typeMsg     = "info";

// Lecture initiale
if (!file_exists($fichier)) {
    die("Erreur : fichier partenaires introuvable.");
}
$partenaires = json_decode(file_get_contents($fichier), true);
if (!is_array($partenaires)) {
    die("Erreur : données partenaires corrompues.");
}

// suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $cible = trim($_POST['nom'] ?? '');

    if ($cible === '') {
        $message = "Partenaire cible manquant.";
        $typeMsg  = "danger";
    } else {
        $nb_avant    = count($partenaires);
        $partenaires = array_values(array_filter($partenaires, fn($p) => $p['nom'] !== $cible));

        if (count($partenaires) === $nb_avant) {
            $message = "Partenaire introuvable.";
            $typeMsg  = "warning";
        } else {
            $resultat = file_put_contents($fichier, json_encode($partenaires, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message  = $resultat !== false ? "Partenaire « $cible » supprimé." : "Erreur lors de la sauvegarde.";
            $typeMsg  = $resultat !== false ? "success" : "danger";
        }
    }
}


// Modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {
    $nomOriginal = trim($_POST['nom_original'] ?? '');
    $nom         = trim($_POST['nom']          ?? '');
    $secteur     = trim($_POST['secteur']      ?? '');
    $contact     = trim($_POST['contact']      ?? '');
    $email       = trim($_POST['email']        ?? '');
    $telephone   = trim($_POST['telephone']    ?? '');
    $description = trim($_POST['description']  ?? '');

    if ($nomOriginal === '' || $nom === '' || $secteur === '' || $contact === '' || $email === '') {
        $message = "Nom, secteur, contact et email sont obligatoires.";
        $typeMsg  = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "L'adresse email n'est pas valide.";
        $typeMsg  = "danger";
    } else {
        $trouve = false;
        foreach ($partenaires as &$p) {
            if ($p['nom'] === $nomOriginal) {
                $p['nom']         = $nom;
                $p['secteur']     = $secteur;
                $p['contact']     = $contact;
                $p['email']       = $email;
                $p['telephone']   = $telephone;
                $p['description'] = $description;
                $trouve = true;
                break;
            }
        }
        unset($p);

        if (!$trouve) {
            $message = "Partenaire introuvable.";
            $typeMsg  = "warning";
        } else {
            $resultat = file_put_contents($fichier, json_encode($partenaires, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message  = $resultat !== false ? "Partenaire « $nom » modifié." : "Erreur lors de la sauvegarde.";
            $typeMsg  = $resultat !== false ? "success" : "danger";
        }
    }
}

$partenaires = json_decode(file_get_contents($fichier), true);

// export
if (isset($_GET['export'])) {
    if ($_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="annuaire_partenaires.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, ['Nom', 'Secteur', 'Contact', 'Email', 'Téléphone', 'Description'], ';');
        foreach ($partenaires as $p) {
            fputcsv($out, [
                $p['nom']         ?? '',
                $p['secteur']     ?? '',
                $p['contact']     ?? '',
                $p['email']       ?? '',
                $p['telephone']   ?? '',
                $p['description'] ?? '',
            ], ';');
        }
        fclose($out);
        exit();
    } elseif ($_GET['export'] === 'txt') {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="annuaire_partenaires.txt"');
        $ligne = str_repeat('-', 80) . "\n";
        echo "ANNUAIRE PARTENAIRES – TechLoc\n";
        echo "Exporté le " . date('d/m/Y à H:i') . "\n";
        echo $ligne;
        foreach ($partenaires as $p) {
            echo "Nom         : " . ($p['nom']         ?? '—') . "\n";
            echo "Secteur     : " . ($p['secteur']     ?? '—') . "\n";
            echo "Contact     : " . ($p['contact']     ?? '—') . "\n";
            echo "Email       : " . ($p['email']       ?? '—') . "\n";
            echo "Téléphone   : " . ($p['telephone']   ?? '—') . "\n";
            echo "Description : " . ($p['description'] ?? '—') . "\n";
            echo $ligne;
        }
        exit();
    }
}

// recherche
$recherche = trim($_GET['recherche'] ?? '');
$partenairesFiltres = $partenaires;
if ($recherche !== '') {
    $partenairesFiltres = array_filter($partenaires, function($p) use ($recherche) {
        return stripos($p['nom']       ?? '', $recherche) !== false
            || stripos($p['secteur']   ?? '', $recherche) !== false
            || stripos($p['contact']   ?? '', $recherche) !== false
            || stripos($p['email']     ?? '', $recherche) !== false
            || stripos($p['telephone'] ?? '', $recherche) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Annuaire Partenaires"); ?>
</head>
<body class="d-flex flex-column min-vh-100" style="background-color:#0F1E38;">
    <?php navigation("annuaire_partenaires", "."); ?>
    <section class="container mt-4">
        <h2 class="mb-1" style="color:#1D9E75;">Annuaire – Partenaires</h2>
        <p style="color:#1D9E75;"><?php echo count($partenaires); ?> partenaire(s) au total.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $typeMsg; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        
        <!-- export -->
        <div class="mb-3 d-flex gap-2">
            <a href="annuaire_partenaires.php?export=csv" class="btn btn-sm fw-bold" style="background-color:#1D9E75; color:white; border:none;">Télécharger CSV</a>
            <a href="annuaire_partenaires.php?export=txt" class="btn btn-sm btn-outline-secondary">Télécharger TXT</a>
        </div>

        <!-- recherche-->
        <form method="GET" action="annuaire_partenaires.php" class="mb-3 d-flex gap-2">
            <input type="text" class="form-control w-auto" name="recherche"
                   placeholder="Rechercher par nom, secteur, contact..."
                   value="<?php echo htmlspecialchars($recherche); ?>">
            <button class="btn" style="color:#1D9E75; border-color:#1D9E75;" type="submit">Rechercher</button>
            <?php if ($recherche): ?>
                <a href="annuaire_partenaires.php" class="btn btn-outline-secondary">Réinitialiser</a>
            <?php endif; ?>
        </form>

        <?php if (empty($partenairesFiltres)): ?>
            <div class="alert alert-info">Aucun partenaire trouvé.</div>
        <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Nom</th>
                    <th>Secteur</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Description</th>
                    <?php if (hasRole('admin', 'manager')): ?>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($partenairesFiltres as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['nom']       ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($p['secteur']   ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($p['contact']   ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($p['email']     ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($p['telephone'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($p['description'] ?? '—'); ?></td>

                    <?php if (hasRole('admin', 'manager')): ?>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalModifier"
                            data-nom="<?php echo htmlspecialchars($p['nom'],             ENT_QUOTES); ?>"
                            data-secteur="<?php echo htmlspecialchars($p['secteur']   ?? '', ENT_QUOTES); ?>"
                            data-contact="<?php echo htmlspecialchars($p['contact']   ?? '', ENT_QUOTES); ?>"
                            data-email="<?php echo htmlspecialchars($p['email']       ?? '', ENT_QUOTES); ?>"
                            data-telephone="<?php echo htmlspecialchars($p['telephone'] ?? '', ENT_QUOTES); ?>"
                            data-description="<?php echo htmlspecialchars($p['description'] ?? '', ENT_QUOTES); ?>">
                            Modifier
                        </button>
                    </td>
                    <td>
                        <form method="POST" action="annuaire_partenaires.php"
                              onsubmit="return confirm('Supprimer « <?php echo htmlspecialchars($p['nom'], ENT_QUOTES); ?> » ?')">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="nom" value="<?php echo htmlspecialchars($p['nom']); ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

    <!-- modal bootstrap pour la modification -->
    <?php if (hasRole('admin', 'manager')): ?>
    <div class="modal fade" id="modalModifier" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier un partenaire</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="annuaire_partenaires.php">
                    <input type="hidden" name="action" value="modifier">
                    <input type="hidden" name="nom_original" id="modal-nom-original">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="nom" id="modal-nom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Secteur *</label>
                            <input type="text" class="form-control" name="secteur" id="modal-secteur" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact *</label>
                            <input type="text" class="form-control" name="contact" id="modal-contact" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" id="modal-email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" name="telephone" id="modal-telephone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="modal-description" rows="3"></textarea>
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
            document.getElementById('modal-nom-original').value  = btn.dataset.nom;
            document.getElementById('modal-nom').value           = btn.dataset.nom;
            document.getElementById('modal-secteur').value       = btn.dataset.secteur;
            document.getElementById('modal-contact').value       = btn.dataset.contact;
            document.getElementById('modal-email').value         = btn.dataset.email;
            document.getElementById('modal-telephone').value     = btn.dataset.telephone;
            document.getElementById('modal-description').value   = btn.dataset.description;
        });
    </script>
    <?php endif; ?>

    <?php piedpage(); ?>
</body>
</html>