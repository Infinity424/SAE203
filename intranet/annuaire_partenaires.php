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