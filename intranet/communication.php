<?php
session_start();
require_once("fonctions.php");

// Lecture du fichier pour le compteur d'utilisateur
$nbUtilisateurs = 0;
$fichier = "./data/SAE203-utilisateurs.json";
if (file_exists($fichier)) {
    $data = json_decode(file_get_contents($fichier), true);
    if (is_array($data)) {
        $nbUtilisateurs = count($data);
    }
}

$erreur = "";
$succes = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom       = trim($_POST['name'] ?? '');
    $entreprise     = trim($_POST['entreprise'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $num     = ($_POST['num'] ?? '');
    $detail = trim($_POST['detail'] ?? '');

    if ($nom === '' || $entreprise === '' || $email === '' || $num === '' || $detail === '') {
        $erreur = "Tous les champs obligatoires doivent être remplis.";
    } else {
        $fichier1  = './data/annuaire_clients.json';
        $annonces = json_decode(file_get_contents($fichier1), true);

        $nouvelleAnnonce = [
            "nom"      => $nom,
            "entreprise"        => $entreprise,
            "email"      => $email,
            "telephone"     => $num,
        ];

        $annonces[] = $nouvelleAnnonce;
        file_put_contents($fichier1, json_encode($annonces, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $succes = "Annonce publiée avec succès !";

        $fichier2  = './data/annuaire_comm.json';
        $annonces = json_decode(file_get_contents($fichier2), true);

        $nouvelleAnnonce = [
            "nom"      => $nom,
            "entreprise"        => $entreprise,
            "email"      => $email,
            "telephone"     => $num,
            "detail"     => $detail,
        ];

        $annonces[] = $nouvelleAnnonce;
        file_put_contents($fichier2, json_encode($annonces, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $succes = "Annonce publiée avec succès !";
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Mon profil"); ?>
</head>
<body style="background-color:#0F1E38;">
    <?php navigation("profil", "."); ?>
    <section class="container mt-4" >
        <h2 class="text-center mb-4" style="color:#1D9E75;">Nouveau client</h2>
                <form method="POST" action="communication.php">
            <div class="row">
                <!-- Infos -->
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Information</h5>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="name" name="name">
                            </div>
                            <div class="mb-3">
                                <label for="entreprise" class="form-label">Entreprise</label>
                                <input type="text" class="form-control" id="entreprise" name="entreprise">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresse email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="mb-3">
                                <label for="num" class="form-label">Numéro de téléphone</label>
                                <input type="tel" class="form-control" id="num" name="num">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message -->
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Précision</h5>
                            <div class="mb-3">
                                <textarea class="form-control" rows="5" id="detail" name="detail"></textarea>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-dark">Envoyer</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($succes): ?>
                    <div class="mb-4" style="background-color:#0F1E38; width: 400px; margin: 0 auto; text-align: center;">
                        <div class="card shadow-sm" style="background-color:#1D9E75;">
                            <div class="card-body">
                                <p>Information bien enregistrée et transmise !</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($erreur): ?>
                    <div class="mb-4" style=" width: 400px; margin: 0 auto; text-align: center;">
                        <div class="card shadow-sm" style="background-color:#FF0000;">
                            <div class="card-body">
                                <p>Erreur dans l'enregistrement des informations !</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </form>
        <h2 class="text-center mb-4" style="color:#1D9E75;">Liste client</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4"> 
        <?php 
        $annonces = json_decode(file_get_contents("./data/annuaire_comm.json"), true);
        foreach ($annonces as $a): 
        ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <span><?php echo htmlspecialchars(ucfirst($a['entreprise'])); ?></span>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <strong>Nom :</strong> <?php echo htmlspecialchars($a['nom']); ?><br>
                            <strong>Email :</strong> <?php echo htmlspecialchars($a['email']); ?><br>
                            <strong>Contact :</strong> <?php echo htmlspecialchars($a['telephone']); ?><br>
                            <strong>Précision :</strong> <?php echo htmlspecialchars($a['detail']); ?><br>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </section>
    <?php piedpage(); ?>
</body>
</html>