<?php
session_start();
require_once("include/fonctions.php");


if (!isset($_SESSION['utilisateur'])) {
    header("Location: ./connexion.php");
    exit();
}

$fichierClients = './data/annuaire_clients.json';
$message = "";
$typeMsg = "info";


if (!file_exists($fichierClients)) {
    die("Erreur : fichier clients introuvable.");
}
$clients = json_decode(file_get_contents($fichierClients), true);
if (!is_array($clients)) {
    die("Erreur : données clients corrompues.");
}

// Supression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $cible = trim($_POST['nom'] ?? '');

    if ($cible === '') {
        $message = "Client cible manquant.";
        $typeMsg  = "danger";
    } else {
        $nb_avant = count($clients);
        $clients  = array_values(array_filter($clients, fn($c) => $c['nom'] !== $cible));

        if (count($clients) === $nb_avant) {
            $message = "Client introuvable.";
            $typeMsg  = "warning";
        } else {
            $resultat = file_put_contents($fichierClients, json_encode($clients, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message  = $resultat !== false ? "Client « $cible » supprimé." : "Erreur lors de la sauvegarde.";
            $typeMsg  = $resultat !== false ? "success" : "danger";
        }
    }
}


// Modification 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {
    $nomOriginal = trim($_POST['nom_original'] ?? '');
    $nom         = trim($_POST['nom']          ?? '');
    $entreprise  = trim($_POST['entreprise']   ?? '');
    $email       = trim($_POST['email']        ?? '');
    $telephone   = trim($_POST['telephone']    ?? '');

    if ($nomOriginal === '' || $nom === '' || $entreprise === '' || $email === '') {
        $message = "Tous les champs obligatoires doivent être remplis.";
        $typeMsg  = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "L'adresse email n'est pas valide.";
        $typeMsg  = "danger";
    } else {
        $trouve = false;
        foreach ($clients as &$c) {
            if ($c['nom'] === $nomOriginal) {
                $c['nom']        = $nom;
                $c['entreprise'] = $entreprise;
                $c['email']      = $email;
                $c['telephone']  = $telephone;
                $trouve = true;
                break;
            }
        }
        unset($c);

        if (!$trouve) {
            $message = "Client introuvable.";
            $typeMsg  = "warning";
        } else {
            $resultat = file_put_contents($fichierClients, json_encode($clients, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $message  = $resultat !== false ? "Client « $nom » modifié." : "Erreur lors de la sauvegarde.";
            $typeMsg  = $resultat !== false ? "success" : "danger";
        }
    }
}

$clients = json_decode(file_get_contents($fichierClients), true);

// export
if (isset($_GET['export'])) {
    $tousClients = $clients;

    if ($_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="annuaire_clients.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, ['Nom', 'Entreprise', 'Email', 'Téléphone'], ';');
        foreach ($tousClients as $c) {
            fputcsv($out, [$c['nom'] ?? '', $c['entreprise'] ?? '', $c['email'] ?? '', $c['telephone'] ?? ''], ';');
        }
        fclose($out);
        exit();
    } elseif ($_GET['export'] === 'txt') {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="annuaire_clients.txt"');
        $ligne = str_repeat('-', 80) . "\n";
        echo "ANNUAIRE CLIENTS – TechLoc\n";
        echo "Exporté le " . date('d/m/Y à H:i') . "\n";
        echo $ligne;
        foreach ($tousClients as $c) {
            echo "Nom        : " . ($c['nom']        ?? '—') . "\n";
            echo "Entreprise : " . ($c['entreprise'] ?? '—') . "\n";
            echo "Email      : " . ($c['email']      ?? '—') . "\n";
            echo "Téléphone  : " . ($c['telephone']  ?? '—') . "\n";
            echo $ligne;
        }
        exit();
    }
}

//  Recherche
$recherche = trim($_GET['recherche'] ?? '');
$clientsFiltres = $clients;
if ($recherche !== '') {
    $clientsFiltres = array_filter($clients, function($c) use ($recherche) {
        return stripos($c['nom']        ?? '', $recherche) !== false
            || stripos($c['email']      ?? '', $recherche) !== false
            || stripos($c['telephone']  ?? '', $recherche) !== false
            || stripos($c['entreprise'] ?? '', $recherche) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Annuaire Clients"); ?>
</head>
<body class="d-flex flex-column min-vh-100" style="background-color:#0F1E38;">
    <?php navigation("annuaire_clients", "."); ?>
    <section class="container mt-4">
        <h2 class="mb-1" style="color:#1D9E75;">Annuaire – Clients</h2>
        <p style="color:#1D9E75;"><?php echo count($clients); ?> client(s) au total.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $typeMsg; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
       
        <div class="mb-3 d-flex gap-2">
            <a href="annuaire_clients.php?export=csv" class="btn btn-sm fw-bold" style="background-color:#1D9E75; color:white; border:none;">Télécharger CSV</a>
            <a href="annuaire_clients.php?export=txt" class="btn btn-sm btn-outline-secondary">Télécharger TXT</a>
        </div>
    
        <form method="GET" action="annuaire_clients.php" class="mb-3 d-flex gap-2">
            <input type="text" class="form-control w-auto" name="recherche"
                   placeholder="Rechercher..."
                   value="<?php echo htmlspecialchars($recherche); ?>">
            <button class="btn" style="color:#1D9E75; border-color:#1D9E75;" type="submit">Rechercher</button>
            <?php if ($recherche): ?>
                <a href="annuaire_clients.php" class="btn btn-outline-secondary">Réinitialiser</a>
            <?php endif; ?>
        </form>
        
        <?php if (empty($clientsFiltres)): ?>
            <div class="alert alert-info">Aucun client trouvé.</div>
        <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Nom</th>
                    <th>Entreprise</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <?php if (hasRole('admin', 'manager')): ?>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($clientsFiltres as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['nom']        ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($c['entreprise'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($c['email']      ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($c['telephone']  ?? '—'); ?></td>

                    <?php if (hasRole('admin', 'manager')): ?>
                    <!-- Bouton modifier → ouvre modal -->
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#modalModifier"
                            data-nom="<?php echo htmlspecialchars($c['nom'],        ENT_QUOTES); ?>"
                            data-entreprise="<?php echo htmlspecialchars($c['entreprise'] ?? '', ENT_QUOTES); ?>"
                            data-email="<?php echo htmlspecialchars($c['email']     ?? '', ENT_QUOTES); ?>"
                            data-telephone="<?php echo htmlspecialchars($c['telephone']  ?? '', ENT_QUOTES); ?>">
                            Modifier
                        </button>
                    </td>
                    
                    <td>
                        <form method="POST" action="annuaire_clients.php"
                              onsubmit="return confirm('Supprimer « <?php echo htmlspecialchars($c['nom'], ENT_QUOTES); ?> » ?')">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="nom" value="<?php echo htmlspecialchars($c['nom']); ?>">
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

    <!-- Modal pour la modification -->
    <?php if (hasRole('admin', 'manager')): ?>
    <div class="modal fade" id="modalModifier" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier un client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="annuaire_clients.php">
                    <input type="hidden" name="action" value="modifier">
                    <input type="hidden" name="nom_original" id="modal-nom-original">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="nom" id="modal-nom" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Entreprise *</label>
                            <input type="text" class="form-control" name="entreprise" id="modal-entreprise" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="email" id="modal-email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" name="telephone" id="modal-telephone">
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
        // Remplit le modal avec les données du client cliqué
        document.getElementById('modalModifier').addEventListener('show.bs.modal', function(e) {
            const btn = e.relatedTarget;
            document.getElementById('modal-nom-original').value = btn.dataset.nom;
            document.getElementById('modal-nom').value          = btn.dataset.nom;
            document.getElementById('modal-entreprise').value   = btn.dataset.entreprise;
            document.getElementById('modal-email').value        = btn.dataset.email;
            document.getElementById('modal-telephone').value    = btn.dataset.telephone;
        });
    </script>
    <?php endif; ?>

    <?php piedpage(); ?>
</body>
</html>