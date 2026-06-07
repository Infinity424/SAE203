<?php
session_start();
require_once("include/fonctions.php");

$fichierAchats = "./data/achats_clients.json";
$msg = "";
$msgClass = "";

$dataAchats = file_exists($fichierAchats) ? json_decode(file_get_contents($fichierAchats), true) : [];
if (!is_array($dataAchats)) $dataAchats = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['entreprise']) && !empty($_POST['montant'])) {
    $montant = floatval($_POST['montant']);
    
    if ($montant <= 0) {
        $msg = "Veuillez entrer un montant valide.";
        $msgClass = "alert-danger";
    } else {
        $maxId = 1000;
        foreach ($dataAchats as $a) {
            $maxId = max($maxId, (int)str_replace('CMD-', '', $a['id_commande'] ?? '0'));
        }

        array_unshift($dataAchats, [
            "id_commande"   => "CMD-" . ($maxId + 1),
            "date"          => trim($_POST['date'] ?? date('Y-m-d')),
            "entreprise"    => trim($_POST['entreprise']),
            "nom"           => trim($_POST['nom'] ?? ''),
            "email"         => trim($_POST['email'] ?? ''),
            "telephone"     => trim($_POST['telephone'] ?? ''),
            "montant_total" => $montant
        ]);

        if (file_put_contents($fichierAchats, json_encode($dataAchats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $msg = "Nouvelle vente enregistrée avec succès !";
            $msgClass = "alert-success"; 
        } else {
            $msg = "Erreur lors de l'enregistrement du fichier.";
            $msgClass = "alert-danger"; 
        }
    }
}


$revenusParClient = [];
foreach ($dataAchats as $achat) {
    if (isset($achat['entreprise'], $achat['montant_total'])) {
        $ent = $achat['entreprise'];
        $revenusParClient[$ent] = ($revenusParClient[$ent] ?? 0) + (float)$achat['montant_total'];
    }
}

arsort($revenusParClient);
$maxRevenu = $revenusParClient ? max($revenusParClient) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Finance"); ?>
</head>
<body class="d-flex flex-column min-vh-100" style="background-color:#0F1E38;">
    <?php navigation("finance", "."); ?>
    
    <section class="container mt-4">
        
        <?php if ($msg): ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="alert <?php echo $msgClass; ?> text-center">
                        <?php echo htmlspecialchars($msg); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <h2 class="text-center mb-4" style="color:#1D9E75;">Total des revenus par client</h2>
        
        <div class="card shadow-sm mb-5">
            <div class="card-body">
                <?php if (empty($revenusParClient)) : ?>
                    <p class="text-center text-muted m-0">Aucune donnée financière n'a pu être chargée.</p>
                <?php else : ?>
                    <?php foreach ($revenusParClient as $entreprise => $total) : 
                        $pourcentage = ($maxRevenu > 0) ? ($total / $maxRevenu) * 100 : 0; 
                    ?>
                        <div class="row align-items-center mb-3">
                            <div class="col-md-3 text-md-end fw-bold">
                                <?php echo htmlspecialchars($entreprise); ?>
                            </div>
                            <div class="col-md-9">
                                <div class="progress" style="height: 30px; background-color: #f5f5f5; overflow: visible !important;">
                                    <div class="progress-bar text-start ps-3 text-nowrap overflow-visible z-1" 
                                         style="width: <?php echo $pourcentage; ?>%; background-color: #1D9E75;">
                                         <span class="fw-bold" style="color: #0F1E38;">
                                            <?php echo number_format($total); ?> €
                                         </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <hr style="border-color: #1D9E75; margin: 40px;">

        <h2 class="text-center mb-4" style="color:#1D9E75;">Nouvelle vente</h2>
        
        <div class="card shadow-sm mb-5">
            <div class="card-body">
                <form method="POST" action="finance.php">
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Entreprise</label>
                            <input type="text" class="form-control" name="entreprise" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Contact (Nom et Prénom)</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Adresse email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Numéro de téléphone</label>
                            <input type="tel" class="form-control" name="telephone" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Montant Total (€)</label>
                            <input type="number" step="1" min="1" class="form-control" name="montant" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date de la commande</label>
                            <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-dark">Enregistrer la vente</button>
                    </div>
                </form>
            </div>
        </div>

    </section>

    <?php piedpage(); ?>
</body>
</html>