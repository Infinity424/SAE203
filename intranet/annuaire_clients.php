<?php
session_start();
require_once("fonctions.php");

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

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Annuaire Clients"); ?>
</head>
<body>
    <?php navigation("annuaire_clients", "."); ?>
    <section class="container mt-4">
        <h2 class="mb-3">Annuaire – Clients</h2>

        <?php if ($message): ?>
            <div class="alert alert-warning"><?php echo htmlspecialchars($message); ?></div>
        <?php else: ?>

        <!-- Recherche -->
        <form method="GET" action="annuaire_clients.php" class="mb-3 d-flex gap-2">
            <input type="text" class="form-control w-auto" name="recherche"
                   placeholder="Rechercher..."
                   value="<?php echo htmlspecialchars($recherche); ?>">
            <button class="btn btn-outline-dark" type="submit">Rechercher</button>
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