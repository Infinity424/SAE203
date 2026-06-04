<?php
session_start();
require_once("include/fonctions.php");

// Accès réservé aux utilisateurs connectés
if (!isset($_SESSION['utilisateur'])) {
    header("Location: ./connexion.php");
    exit();
}

$fichier     = './data/annuaire_partenaires.json';
$partenaires = [];
$message     = "";

if (!file_exists($fichier)) {
    $message = "Le fichier de données partenaires est introuvable.";
} else {
    $contenu = file_get_contents($fichier);
    $data    = json_decode($contenu, true);
    if (!is_array($data)) {
        $message = "Les données partenaires sont corrompues.";
    } else {
        $partenaires = $data;
    }
}

// Recherche
$recherche = trim($_GET['recherche'] ?? '');
if ($recherche !== '' && !empty($partenaires)) {
    $partenaires = array_filter($partenaires, function($p) use ($recherche) {
        return stripos($p['nom']        ?? '', $recherche) !== false
            || stripos($p['secteur']    ?? '', $recherche) !== false
            || stripos($p['contact']    ?? '', $recherche) !== false
            || stripos($p['telephone']    ?? '', $recherche) !== false
            || stripos($p['email']      ?? '', $recherche) !== false;
    });
}

//export
 
if (isset($_GET['export'])) {
 
    $tousPartenaires = json_decode(file_get_contents($fichier), true) ?? [];
 
    if ($_GET['export'] === 'csv') {
 
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="annuaire_partenaires.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 pour Excel
        fputcsv($out, ['Nom', 'Secteur', 'Contact', 'Email', 'Téléphone', 'Description'], ';');
        foreach ($tousPartenaires as $p) {
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
        foreach ($tousPartenaires as $p) {
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Annuaire Partenaires"); ?>
</head>
<body style="background-color:#0F1E38;">
    <?php navigation("annuaire_partenaires", "."); ?>
    <section class="container mt-4">
        <h2 class="mb-3" style="color:#1D9E75;" >Annuaire – Partenaires</h2>
        <div class="mb-3 d-flex gap-2">
            <a href="annuaire_partenaires.php?export=csv" class="btn btn-sm fw-bold"
            style="background-color:#1D9E75; color:white; border:none;">
                Télécharger CSV
            </a>
            <a href="annuaire_partenaires.php?export=txt" class="btn btn-sm btn-outline-secondary">
                Télécharger TXT
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-warning"><?php echo htmlspecialchars($message); ?></div>
        <?php else: ?>

        <!-- Recherche -->
        <form method="GET" action="annuaire_partenaires.php" class="mb-3 d-flex gap-2">
            <input type="text" class="form-control w-auto" name="recherche"
                   placeholder="Rechercher par nom, secteur, contact..."
                   value="<?php echo htmlspecialchars($recherche); ?>">
            <button class="btn btn-outline-dark" style="color:#1D9E75;  border-color:#1D9E75;" type="submit">Rechercher</button>
            <?php if ($recherche): ?>
                <a href="annuaire_partenaires.php" class="btn btn-outline-secondary">Réinitialiser</a>
            <?php endif; ?>
        </form>

        <?php if (empty($partenaires)): ?>
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
                </tr>
            </thead>
            <tbody>
            <?php foreach ($partenaires as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['nom']       ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($p['secteur']   ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($p['contact']   ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($p['email']     ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($p['telephone'] ?? '—'); ?></td>
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