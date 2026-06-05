<?php
session_start();
require_once("include/fonctions.php");

// Accès réservé aux utilisateurs connectés
if (!isset($_SESSION['utilisateur'])) {
    header("Location: ./connexion.php");
    exit();
}

$fichier  = './data/SAE203-utilisateurs.json';
$membres  = [];
$message  = "";

if (!file_exists($fichier)) {
    $message = "Le fichier de données entreprise est introuvable.";
} else {
    $contenu = file_get_contents($fichier);
    $data    = json_decode($contenu, true);
    if (!is_array($data)) {
        $message = "Les données entreprise sont corrompues.";
    } else {
        $membres = $data;
    }
}

// Recherche
$recherche = trim($_GET['recherche'] ?? '');
if ($recherche !== '' && !empty($membres)) {
    $membres = array_filter($membres, function($m) use ($recherche) {
        return stripos($m['utilisateur']        ?? '', $recherche) !== false
            || stripos($m['role']      ?? '', $recherche) !== false
            || stripos($m['email']      ?? '', $recherche) !== false;
    });
}

// Export
if (isset($_GET['export'])) {
    $tousMembres = json_decode(file_get_contents($fichier), true) ?? [];
    if ($_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="annuaire_entreprise.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 pour Excel
        fputcsv($out, ['Nom', 'Service / Rôle', 'Email'], ';');
        foreach ($tousMembres as $m) {
            fputcsv($out, [
                $m['utilisateur'] ?? '',
                $m['role']        ?? '',
                $m['email']       ?? '',
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
        foreach ($tousMembres as $m) {
            echo "Nom     : " . ($m['utilisateur'] ?? '—') . "\n";
            echo "Service : " . ($m['role']        ?? '—') . "\n";
            echo "Email   : " . ($m['email']       ?? '—') . "\n";
            echo $ligne;
        }
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Annuaire Entreprise"); ?>
</head>
<body style="background-color:#0F1E38;">
    <?php navigation("annuaire_entreprise", "."); ?>
    <section class="container mt-4">
        <h2 class="mb-3"style="color:#1D9E75;">Annuaire – Entreprise</h2>
        <div class="mb-3 d-flex gap-2">
            <a href="annuaire_entreprise.php?export=csv" class="btn btn-sm fw-bold"
            style="background-color:#1D9E75; color:white; border:none;">
                Télécharger CSV
            </a>
            <a href="annuaire_entreprise.php?export=txt" class="btn btn-sm btn-outline-secondary">
                Télécharger TXT
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-warning"><?php echo htmlspecialchars($message); ?></div>
        <?php else: ?>

        <!-- Recherche -->
        <form method="GET" action="annuaire_entreprise.php" class="mb-3 d-flex gap-2">
            <input type="text" class="form-control w-auto" name="recherche"
                   placeholder="Rechercher par nom, service..."
                   value="<?php echo htmlspecialchars($recherche); ?>">
            <button class="btn btn-outline-dark" style="color:#1D9E75;  border-color:#1D9E75;" type="submit">Rechercher</button>
            <?php if ($recherche): ?>
                <a href="annuaire_entreprise.php" class="btn btn-outline-secondary">Réinitialiser</a>
            <?php endif; ?>
        </form>

        <?php if (empty($membres)): ?>
            <div class="alert alert-info">Aucun membre trouvé.</div>
        <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Nom</th>
                    <th>Service</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($membres as $m): ?>
                <tr>
                    <td><?php echo htmlspecialchars($m['utilisateur']       ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($m['role']   ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($m['email']     ?? '—'); ?></td>
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