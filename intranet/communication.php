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
    $typePost   = $_POST['type_form'] ?? '';
    $entreprise = trim($_POST['entreprise'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $num        = ($_POST['num'] ?? '');
    $detail     = trim($_POST['detail'] ?? '');
    $cheminLogo = '';

    // Champs spécifiques selon le type
    if ($typePost === 'client') {
        $nom = trim($_POST['name'] ?? '');
        $champsVides = ($nom === '' || $entreprise === '' || $email === '' || $num === '' || $detail === '');
    } elseif ($typePost === 'partenaire') {
        $contact = trim($_POST['contact'] ?? '');
        $secteur = trim($_POST['secteur'] ?? '');
        $champsVides = ($contact === '' || $secteur === '' || $entreprise === '' || $email === '' || $num === '' || $detail === '');
    } else {
        $champsVides = true;
    }

    if ($champsVides) {
        $erreur = "Tous les champs obligatoires doivent être remplis.";
    } elseif (empty($_FILES['logo']['name'])) {
        $erreur = "Le logo est obligatoire.";
    } else {
        $dossierImg = './img/';
        if (!is_dir($dossierImg)) mkdir($dossierImg, 0755, true);

        $extension   = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $nomFichier  = uniqid('logo_') . '.' . strtolower($extension);
        $destination = $dossierImg . $nomFichier;
        $cheminLogo  = $destination;
        move_uploaded_file($_FILES['logo']['tmp_name'], $destination);

        if ($typePost === 'client') {
            $fichier1 = './data/annuaire_clients.json';
            $liste = json_decode(file_get_contents($fichier1), true);
            $liste[] = [
                "nom"       => $nom,
                "entreprise"=> $entreprise,
                "email"     => $email,
                "telephone" => $num,
            ];
            file_put_contents($fichier1, json_encode($liste, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $fichier2 = './data/annuaire_commcl.json';
            $liste2 = json_decode(file_get_contents($fichier2), true);
            $liste2[] = [
                "nom"       => $nom,
                "entreprise"=> $entreprise,
                "email"     => $email,
                "telephone" => $num,
                "detail"    => $detail,
                "logo"      => $cheminLogo,
            ];
            file_put_contents($fichier2, json_encode($liste2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        } elseif ($typePost === 'partenaire') {
            $fichier1 = './data/annuaire_partenaires.json';
            $liste = json_decode(file_get_contents($fichier1), true);
            $liste[] = [
                "nom"       => $entreprise,
                "secteur"   => $secteur,
                "contact"   => $contact,
                "email"     => $email,
                "telephone" => $num,
            ];
            file_put_contents($fichier1, json_encode($liste, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $fichier2 = './data/annuaire_commpar.json';
            $liste2 = json_decode(file_get_contents($fichier2), true);
            $liste2[] = [
                "nom"       => $entreprise,
                "secteur"   => $secteur,
                "contact"   => $contact,
                "email"     => $email,
                "telephone" => $num,
                "detail"    => $detail,
                "logo"      => $cheminLogo,
            ];
            file_put_contents($fichier2, json_encode($liste2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

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
    <?php
         $type = $_GET['type'] ?? '';
    ?>
    <section class="container mt-4" >
    <?php if ($type === 'client') : ?>
        <h2 class="text-center mb-4" style="color:#1D9E75;">Nouveau client</h2>
                <form method="POST" action="communication.php" enctype="multipart/form-data">
                    <input type="hidden" name="type_form" value="client">
            <div class="row">
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
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Précision</h5>
                            <div class="mb-3">
                                <textarea class="form-control mb-4" rows="5" id="detail" name="detail"></textarea>
                                <label for="avatar">Logo:</label>
                                <input type="file" id="logo" name="logo" accept="image/png, image/jpeg" />
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
        $annonces = json_decode(file_get_contents("./data/annuaire_commcl.json"), true);
        foreach ($annonces as $a): 
        ?>
            <div class="col mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <span><?php echo htmlspecialchars(ucfirst($a['entreprise'])); ?></span>
                            <img src="<?php echo htmlspecialchars($a['logo']); ?>" 
                                alt="Logo" class="img-fluid mb-2" style="max-height: 80px;">
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
        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <h2 class="text-center mb-4" style="color:#1D9E75;">Changer de formulaire ci-dessous.</h2>
                <div class="text-center mb-4">
                        <a href="?type=client" class="btn btn-sm ms-2" style="color:#1D9E75;  border-color:#1D9E75;">Formulaire Clients</a>
                        <a href="?type=partenaire" class="btn btn-sm ms-2" style="color:#1D9E75;  border-color:#1D9E75;">Formulaire Partenaires</a>
                </div>
            </div>
        </div>
    <?php elseif ($type === 'partenaire') : ?>
        <h2 class="text-center mb-4" style="color:#1D9E75;">Nouveau partenaire</h2>
        <form method="POST" action="communication.php" enctype="multipart/form-data">
            <input type="hidden" name="type_form" value="partenaire">
            <div class="row">
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Information</h5>
                            <div class="mb-3">
                                <label for="contact" class="form-label">Contact</label>
                                <input type="text" class="form-control" id="contact" name="contact">
                            </div>
                            <div class="mb-3">
                                <label for="entreprise" class="form-label">Entreprise</label>
                                <input type="text" class="form-control" id="entreprise" name="entreprise">
                            </div>
                            <div class="mb-3">
                                <label for="secteur" class="form-label">Secteur d'activité</label>
                                <input type="text" class="form-control" id="secteur" name="secteur">
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
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Précision</h5>
                            <div class="mb-3">
                                <textarea class="form-control mb-4" rows="5" id="detail" name="detail"></textarea>
                                <label for="logo">Logo :</label>
                                <input type="file" id="logo" name="logo" accept="image/png, image/jpeg" />
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-dark">Envoyer</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($succes): ?>
                    <div class="mb-4" style="background-color:#0F1E38; width:400px; margin:0 auto; text-align:center;">
                        <div class="card shadow-sm" style="background-color:#1D9E75;">
                            <div class="card-body"><p>Information bien enregistrée et transmise !</p></div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($erreur): ?>
                    <div class="mb-4" style="width:400px; margin:0 auto; text-align:center;">
                        <div class="card shadow-sm" style="background-color:#FF0000;">
                            <div class="card-body"><p>Erreur dans l'enregistrement des informations !</p></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </form>

        <h2 class="text-center mb-4" style="color:#1D9E75;">Liste partenaires</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php
        $annonces = json_decode(file_get_contents("./data/annuaire_commpar.json"), true);
        foreach ($annonces as $a):
        ?>
            <div class="col mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <span><?php echo htmlspecialchars(ucfirst($a['nom'])); ?></span>
                        <img src="<?php echo htmlspecialchars($a['logo']); ?>" alt="Logo" class="img-fluid mb-2" style="max-height:80px;">
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <strong>Contact :</strong> <?php echo htmlspecialchars($a['contact']); ?><br>
                            <strong>Secteur :</strong> <?php echo htmlspecialchars($a['secteur']); ?><br>
                            <strong>Email :</strong> <?php echo htmlspecialchars($a['email']); ?><br>
                            <strong>Téléphone :</strong> <?php echo htmlspecialchars($a['telephone']); ?><br>
                            <strong>Précision :</strong> <?php echo htmlspecialchars($a['detail']); ?><br>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <h2 class="text-center mb-4" style="color:#1D9E75;">Changer de formulaire ci-dessous.</h2>
                <div class="text-center mb-4">
                    <a href="?type=client" class="btn btn-sm ms-2" style="color:#1D9E75; border-color:#1D9E75;">Formulaire Clients</a>
                    <a href="?type=partenaire" class="btn btn-sm ms-2" style="color:#1D9E75; border-color:#1D9E75;">Formulaire Partenaires</a>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4" style="color:#1D9E75;">Choisissez un formulaire ci-dessous.</h2>
                <div class="text-center mb-4">
                        <a href="?type=client" class="btn btn-sm ms-2" style="color:#1D9E75;  border-color:#1D9E75;">Formulaire Clients</a>
                        <a href="?type=partenaire" class="btn btn-sm ms-2" style="color:#1D9E75;  border-color:#1D9E75;">Formulaire Partenaires</a>
                </div>
            </div>
        </div>
        <h2 class="text-center mb-4" style="color:#1D9E75;">Liste client</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4"> 
        <?php 
        $annonces = json_decode(file_get_contents("./data/annuaire_commcl.json"), true);
        foreach ($annonces as $a): 
        ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <span><?php echo htmlspecialchars(ucfirst($a['entreprise'])); ?></span>
                            <img src="<?php echo htmlspecialchars($a['logo']); ?>" 
                                alt="Logo" class="img-fluid mb-2" style="max-height: 80px;">
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
        <h2 class="text-center mb-4" style="color:#1D9E75;">Liste partenaires</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
        <?php
        $annonces = json_decode(file_get_contents("./data/annuaire_commpar.json"), true);
        foreach ($annonces as $a):
        ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <span><?php echo htmlspecialchars(ucfirst($a['nom'])); ?></span>
                        <img src="<?php echo htmlspecialchars($a['logo']); ?>" alt="Logo" class="img-fluid mb-2" style="max-height:80px;">
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <strong>Contact :</strong> <?php echo htmlspecialchars($a['contact']); ?><br>
                            <strong>Secteur :</strong> <?php echo htmlspecialchars($a['secteur']); ?><br>
                            <strong>Email :</strong> <?php echo htmlspecialchars($a['email']); ?><br>
                            <strong>Téléphone :</strong> <?php echo htmlspecialchars($a['telephone']); ?><br>
                            <strong>Précision :</strong> <?php echo htmlspecialchars($a['detail']); ?><br>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

    </section>
    <?php piedpage(); ?>
</body>
</html>