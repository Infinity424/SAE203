<?php
session_start();
require_once("include/fonctions.php");

$nbUtilisateurs = 0;
$fichier = "./data/SAE203-utilisateurs.json";
if (file_exists($fichier)) {
    $data = json_decode(file_get_contents($fichier), true);
    if (is_array($data)) {
        $nbUtilisateurs = count($data);
    }
}
$fichierAchats = "./data/achats_clients.json";
$revenusParClient = [];
$maxRevenu = 0;

if (file_exists($fichierAchats)) {
    $dataAchats = json_decode(file_get_contents($fichierAchats), true);
    if (is_array($dataAchats)) {
        foreach ($dataAchats as $achat) {
            $entreprise = $achat['entreprise'];
            $montant = $achat['montant_total'];
            
            if (!isset($revenusParClient[$entreprise])) {
                $revenusParClient[$entreprise] = 0;
            }
            $revenusParClient[$entreprise] += $montant;
        }
    }
}


arsort($revenusParClient);

if (!empty($revenusParClient)) {
    $maxRevenu = max($revenusParClient);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Finance"); ?>
    <style>
        .chart-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .chart-title {
            text-align: center;
            color: #0F1E38;
            margin-bottom: 35px;
            font-size: 22px;
            font-weight: bold;
        }
        .bar-row {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
        }
        .bar-label {
            width: 220px;
            text-align: right;
            padding-right: 15px;
            font-weight: bold;
            font-size: 14px;
        }
        .bar-track {
            flex-grow: 1;
            position: relative;
            height: 30px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }
        .bar-fill {
            background: #1D9E75;
            height: 100%;
            border-radius: 4px;
        }
        .bar-value {
            position: absolute;
            top: 0;
            line-height: 30px;
            font-size: 13px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100" style="background-color:#0F1E38;">
    <?php navigation("finance", "."); ?>
    
    <section class="container mt-4">
        
        <div class="chart-card">
            <div class="chart-title">Total des revenus par client</div>
            
            <?php if (!empty($revenusParClient)) : ?>
                <?php foreach ($revenusParClient as $entreprise => $total) : ?>
                    <?php 
                        $pourcentage = ($maxRevenu > 0) ? ($total / $maxRevenu) * 90 : 0; 
                    ?>
                    <div class="bar-row">
                        <div class="bar-label"><?php echo htmlspecialchars($entreprise); ?></div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?php echo $pourcentage; ?>%;"></div>
                            <div class="bar-value" style="left: calc(<?php echo $pourcentage; ?>% + 12px);">
                                <?php echo number_format($total, 0, ',', ' '); ?> €
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p class="text-center text-muted m-0">Aucune donnée financière n'a pu être chargée.</p>
            <?php endif; ?>
        </div>
        <!-- https://canvasjs.com/docs/charts/chart-types/html5-bar-chart/
         https://www.pierre-giraud.com/bootstrap-apprendre-cours/progress/ -->

    </section>

    <?php piedpage(); ?>
</body>
</html>