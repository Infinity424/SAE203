<?php
session_start();
require_once("include/fonctions.php");

// Accès réservé aux utilisateurs connectés
if (!isset($_SESSION['utilisateur'])) {
    header("Location: ./connexion.php");
    exit();
}

// Lecture du fichier annuaire clients
$fichierClients = './data/annuaire_clients.json';
$clients = [];
$message = "";

if (!file_exists($fichierClients)) {
    $message = "Le fichier de données clients est introuvable.";
} else {
    $contenu = file_get_contents($fichierClients);
    $data    = json_decode($contenu, true);
    if (!is_array($data)) {
        $message = "Les données clients sont corrompues.";
    } else {
        $clients = $data;
    }
}

// Recherche
$recherche = trim($_GET['recherche'] ?? '');
if ($recherche !== '' && !empty($clients)) {
    $clients = array_filter($clients, function($c) use ($recherche) {
        return stripos($c['nom']          ?? '', $recherche) !== false
            || stripos($c['email']        ?? '', $recherche) !== false
            || stripos($c['telephone']    ?? '', $recherche) !== false
            || stripos($c['entreprise']   ?? '', $recherche) !== false;
    });
}

//export
if (isset($_GET['export'])) {
 
    // Relire les données (les filtres de recherche ne s'appliquent pas à l'export)
    $tousClients = json_decode(file_get_contents($fichierClients), true) ?? [];
 
    $colonnes = ['nom', 'entreprise', 'email', 'telephone'];
 
    if ($_GET['export'] === 'csv') {
 
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="annuaire_clients.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 pour Excel
        fputcsv($out, ['Nom', 'Entreprise', 'Email', 'Téléphone'], ';');
        foreach ($tousClients as $c) {
            fputcsv($out, [
                $c['nom']        ?? '',
                $c['entreprise'] ?? '',
                $c['email']      ?? '',
                $c['telephone']  ?? '',
            ], ';');
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

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Annuaire Clients"); ?>
</head>
<body class="d-flex flex-column min-vh-100" style="background-color:#0F1E38;">
    <?php navigation("annuaire_clients", "."); ?>
    <section class="container mt-4">
        <h2 class="mb-3" style="color:#1D9E75;">Annuaire – Clients</h2>
        <div class="mb-3 d-flex gap-2">
            <a href="annuaire_clients.php?export=csv" class="btn btn-sm fw-bold"
            style="background-color:#1D9E75; color:white; border:none;">
                Télécharger CSV
            </a>
            <a href="annuaire_clients.php?export=txt" class="btn btn-sm btn-outline-secondary">
                Télécharger TXT
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-warning"><?php echo htmlspecialchars($message); ?></div>
        <?php else: ?>

        <!-- Recherche -->
        <form method="GET" action="annuaire_clients.php" class="mb-3 d-flex gap-2">
            <input type="text" class="form-control w-auto" name="recherche"
                   placeholder="Rechercher..."
                   value="<?php echo htmlspecialchars($recherche); ?>">
            <button class="btn btn-outline-dark" style="color:#1D9E75;  border-color:#1D9E75;" type="submit">Rechercher</button>
            <?php if ($recherche): ?>
                <a href="annuaire_clients.php" class="btn btn-outline-secondary">Réinitialiser</a>
            <?php endif; ?>
        </form>

        <?php if (empty($clients)): ?>
            <div class="alert alert-info">Aucun client trouvé.</div>
        <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Nom</th>
                    <th>Entreprise</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($clients as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['nom']        ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($c['entreprise'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($c['email']      ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($c['telephone']  ?? '—'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php piedpage(); ?>
</body>
</html>