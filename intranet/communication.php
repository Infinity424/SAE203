<?php
session_start();
require_once("include/fonctions.php");

// Accès réservé aux rôles ayant la communication
if (!estConnecte() || !hasRole('admin', 'manager', 'modo', 'com')) {
    header("Location: ./accueil.php");
    exit();
}

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

// SUPPRESSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $typeSuppr = $_POST['type_liste'] ?? '';
    $index     = (int)($_POST['index'] ?? -1);

    if ($typeSuppr === 'client') {
        $f1   = './data/annuaire_commcl.json';
        $data = json_decode(file_get_contents($f1), true);

        $emailCible = $data[$index]['email'] ?? null;  // On retient l'email avant suppression
        $logoCible  = $data[$index]['logo']  ?? null;

        if (isset($data[$index])) array_splice($data, $index, 1);
        file_put_contents($f1, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        if ($logoCible && file_exists($logoCible)) {
            unlink($logoCible);
        }

        if ($emailCible) {
            $f2    = './data/annuaire_clients.json';
            $data2 = json_decode(file_get_contents($f2), true);
            $logoCible = $data[$index]['logo'] ?? null;
            $data2 = array_values(array_filter($data2, function($c) use ($emailCible) {return $c['email'] !== $emailCible;}));
            file_put_contents($f2, json_encode($data2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

    } elseif ($typeSuppr === 'partenaire') {
        $f1 = './data/annuaire_commpar.json';
        $data = json_decode(file_get_contents($f1), true);
        if (isset($data[$index])) array_splice($data, $index, 1);
        file_put_contents($f1, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $f2 = './data/annuaire_partenaires.json';
        $data2 = json_decode(file_get_contents($f2), true);
        if (isset($data2[$index])) array_splice($data2, $index, 1);
        file_put_contents($f2, json_encode($data2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    $succes = "Entrée supprimée avec succès.";

// MODIFICATION
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {
    $typeMod = $_POST['type_liste'] ?? '';
    $index   = (int)($_POST['index'] ?? -1);

    if ($typeMod === 'client') {
        $f = './data/annuaire_commcl.json';
        $data = json_decode(file_get_contents($f), true);
        if (isset($data[$index])) {
            $data[$index]['nom']        = trim($_POST['nom'] ?? '');
            $data[$index]['entreprise'] = trim($_POST['entreprise'] ?? '');
            $data[$index]['email']      = trim($_POST['email'] ?? '');
            $data[$index]['telephone']  = trim($_POST['telephone'] ?? '');
            $data[$index]['detail']     = trim($_POST['detail'] ?? '');
            if (!empty($_FILES['logo']['name'])) {
                $dossierImg = './img/';
                if (!is_dir($dossierImg)) mkdir($dossierImg, 0755, true);
                $ext  = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $dest = $dossierImg . uniqid('logo_') . '.' . strtolower($ext);
                move_uploaded_file($_FILES['logo']['tmp_name'], $dest);
                $data[$index]['logo'] = $dest;
            }
            file_put_contents($f, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $f2 = './data/annuaire_clients.json';
            $data2 = json_decode(file_get_contents($f2), true);
            if (isset($data2[$index])) {
                $data2[$index]['nom']        = $data[$index]['nom'];
                $data2[$index]['entreprise'] = $data[$index]['entreprise'];
                $data2[$index]['email']      = $data[$index]['email'];
                $data2[$index]['telephone']  = $data[$index]['telephone'];
                file_put_contents($f2, json_encode($data2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    } elseif ($typeMod === 'partenaire') {
        $f = './data/annuaire_commpar.json';
        $data = json_decode(file_get_contents($f), true);
        if (isset($data[$index])) {
            $data[$index]['nom']       = trim($_POST['nom'] ?? '');
            $data[$index]['secteur']   = trim($_POST['secteur'] ?? '');
            $data[$index]['contact']   = trim($_POST['contact'] ?? '');
            $data[$index]['email']     = trim($_POST['email'] ?? '');
            $data[$index]['telephone'] = trim($_POST['telephone'] ?? '');
            $data[$index]['detail']    = trim($_POST['detail'] ?? '');
            if (!empty($_FILES['logo']['name'])) {
                $dossierImg = './img/';
                if (!is_dir($dossierImg)) mkdir($dossierImg, 0755, true);
                $ext  = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $dest = $dossierImg . uniqid('logo_') . '.' . strtolower($ext);
                move_uploaded_file($_FILES['logo']['tmp_name'], $dest);
                $data[$index]['logo'] = $dest;
            }
            file_put_contents($f, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $f2 = './data/annuaire_partenaires.json';
            $data2 = json_decode(file_get_contents($f2), true);
            if (isset($data2[$index])) {
                $data2[$index]['nom']       = $data[$index]['nom'];
                $data2[$index]['secteur']   = $data[$index]['secteur'];
                $data2[$index]['contact']   = $data[$index]['contact'];
                $data2[$index]['email']     = $data[$index]['email'];
                $data2[$index]['telephone'] = $data[$index]['telephone'];
                file_put_contents($f2, json_encode($data2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }
    $succes = "Entrée modifiée avec succès !";

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $typePost   = $_POST['type_form'] ?? '';
    $entreprise = trim($_POST['entreprise'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $num        = ($_POST['num'] ?? '');
    $detail     = trim($_POST['detail'] ?? '');
    $cheminLogo = '';

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
            $liste[] = ["nom" => $nom, "entreprise" => $entreprise, "email" => $email, "telephone" => $num];
            file_put_contents($fichier1, json_encode($liste, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $fichier2 = './data/annuaire_commcl.json';
            $liste2 = json_decode(file_get_contents($fichier2), true);
            $liste2[] = ["nom" => $nom, "entreprise" => $entreprise, "email" => $email, "telephone" => $num, "detail" => $detail, "logo" => $cheminLogo];
            file_put_contents($fichier2, json_encode($liste2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        } elseif ($typePost === 'partenaire') {
            $fichier1 = './data/annuaire_partenaires.json';
            $liste = json_decode(file_get_contents($fichier1), true);
            $liste[] = ["nom" => $entreprise, "secteur" => $secteur, "contact" => $contact, "email" => $email, "telephone" => $num];
            file_put_contents($fichier1, json_encode($liste, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $fichier2 = './data/annuaire_commpar.json';
            $liste2 = json_decode(file_get_contents($fichier2), true);
            $liste2[] = ["nom" => $entreprise, "secteur" => $secteur, "contact" => $contact, "email" => $email, "telephone" => $num, "detail" => $detail, "logo" => $cheminLogo];
            file_put_contents($fichier2, json_encode($liste2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        $succes = "Entrée modifiée avec succès !";
    }
}
$editerIndex = isset($_GET['editer']) ? (int)$_GET['editer'] : -1;
$editerType  = $_GET['type_liste'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Communication"); ?>
</head>
<body style="background-color:#0F1E38;">
    <?php navigation("communication", "."); ?>
    <?php
         $type = $_GET['type'] ?? '';
    ?>
    <section class="container mt-4" >
    <?php 
    if ($type === 'client') : ?>
        <h2 class="text-center mb-4" style="color:#1D9E75;">Nouveau client</h2>
                <form method="POST" action="communication.php?type=client" enctype="multipart/form-data">
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
        <h2 class="text-center mb-4" style="color:#1D9E75;">Liste client</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4"> 
        <?php 
        $annonces = json_decode(file_get_contents("./data/annuaire_commcl.json"), true);
        foreach ($annonces as $i => $a):
            $enEdition = ($editerIndex === $i && $editerType === 'client'); 
        ?>
            <div class="col">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span><?php echo htmlspecialchars(ucfirst($a['entreprise'])); ?></span>
                        <img src="<?php echo htmlspecialchars($a['logo']); ?>" 
                            alt="Logo" class="img-fluid" style="max-height: 80px;">
                    </div>

                    <?php if ($enEdition): ?>
                        <div class="card-body">
                            <form method="POST" action="communication.php?type=client" enctype="multipart/form-data">
                                <input type="hidden" name="action"     value="modifier">
                                <input type="hidden" name="type_liste" value="client">
                                <input type="hidden" name="index"      value="<?php echo $i; ?>">
                                <div class="mb-2">
                                    <label class="form-label">Nom</label>
                                    <input type="text" class="form-control form-control-sm" name="nom"
                                        value="<?php echo htmlspecialchars($a['nom']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Entreprise</label>
                                    <input type="text" class="form-control form-control-sm" name="entreprise"
                                        value="<?php echo htmlspecialchars($a['entreprise']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control form-control-sm" name="email"
                                        value="<?php echo htmlspecialchars($a['email']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control form-control-sm" name="telephone"
                                        value="<?php echo htmlspecialchars($a['telephone']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Précision</label>
                                    <textarea class="form-control form-control-sm" rows="3"
                                            name="detail"><?php echo htmlspecialchars($a['detail']); ?></textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Nouveau logo (optionnel)</label>
                                    <input type="file" class="form-control form-control-sm" name="logo"
                                        accept="image/png, image/jpeg">
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button type="submit" class="btn btn-success btn-sm">💾 Sauvegarder</button>
                                    <a href="communication.php?type=client" class="btn btn-outline-secondary btn-sm">Annuler</a>
                                </div>
                            </form>
                        </div>

                    <?php else: ?>
                        <div class="card-body">
                            <p class="card-text">
                                <strong>Nom :</strong> <?php echo htmlspecialchars($a['nom']); ?><br>
                                <strong>Email :</strong> <?php echo htmlspecialchars($a['email']); ?><br>
                                <strong>Contact :</strong> <?php echo htmlspecialchars($a['telephone']); ?><br>
                                <strong>Précision :</strong> <?php echo htmlspecialchars($a['detail']); ?><br>
                            </p>
                        </div>
                        <div class="card-footer d-flex gap-2">
                            <a href="communication.php?type=client&editer=<?php echo $i; ?>&type_liste=client"
                            class="btn btn-outline-primary btn-sm">✏️ Modifier</a>
                            <form method="POST" action="communication.php?type=client"
                                onsubmit="return confirm('Supprimer ce client ?')">
                                <input type="hidden" name="action"     value="supprimer">
                                <input type="hidden" name="type_liste" value="client">
                                <input type="hidden" name="index"      value="<?php echo $i; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">🗑️ Supprimer</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php elseif ($type === 'partenaire') : ?>
        <h2 class="text-center mb-4" style="color:#1D9E75;">Nouveau partenaire</h2>
        <form method="POST" action="communication.php?type=partenaire" enctype="multipart/form-data">
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
        <h2 class="text-center mb-4" style="color:#1D9E75;">Liste partenaires</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php
        $annonces = json_decode(file_get_contents("./data/annuaire_commpar.json"), true);
        foreach ($annonces as $i => $a):
            $enEdition = ($editerIndex === $i && $editerType === 'partenaire');
        ?>
            <div class="col">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span><?php echo htmlspecialchars(ucfirst($a['nom'])); ?></span>
                        <img src="<?php echo htmlspecialchars($a['logo']); ?>" alt="Logo" class="img-fluid" style="max-height:50px;">
                    </div>

                    <?php if ($enEdition): ?>
                        <div class="card-body">
                            <form method="POST" action="communication.php?type=partenaire" enctype="multipart/form-data">
                                <input type="hidden" name="action"     value="modifier">
                                <input type="hidden" name="type_liste" value="partenaire">
                                <input type="hidden" name="index"      value="<?php echo $i; ?>">
                                <div class="mb-2">
                                    <label class="form-label">Entreprise</label>
                                    <input type="text" class="form-control form-control-sm" name="nom"
                                        value="<?php echo htmlspecialchars($a['nom']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Secteur</label>
                                    <input type="text" class="form-control form-control-sm" name="secteur"
                                        value="<?php echo htmlspecialchars($a['secteur']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Contact</label>
                                    <input type="text" class="form-control form-control-sm" name="contact"
                                        value="<?php echo htmlspecialchars($a['contact']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control form-control-sm" name="email"
                                        value="<?php echo htmlspecialchars($a['email']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control form-control-sm" name="telephone"
                                        value="<?php echo htmlspecialchars($a['telephone']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Précision</label>
                                    <textarea class="form-control form-control-sm" rows="3"
                                            name="detail"><?php echo htmlspecialchars($a['detail']); ?></textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Nouveau logo (optionnel)</label>
                                    <input type="file" class="form-control form-control-sm" name="logo"
                                        accept="image/png, image/jpeg">
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button type="submit" class="btn btn-success btn-sm">💾 Sauvegarder</button>
                                    <a href="communication.php?type=partenaire" class="btn btn-outline-secondary btn-sm">Annuler</a>
                                </div>
                            </form>
                        </div>

                    <?php else: ?>
                        <div class="card-body">
                            <p class="card-text">
                                <strong>Contact :</strong> <?php echo htmlspecialchars($a['contact']); ?><br>
                                <strong>Secteur :</strong> <?php echo htmlspecialchars($a['secteur']); ?><br>
                                <strong>Email :</strong> <?php echo htmlspecialchars($a['email']); ?><br>
                                <strong>Téléphone :</strong> <?php echo htmlspecialchars($a['telephone']); ?><br>
                                <strong>Précision :</strong> <?php echo htmlspecialchars($a['detail']); ?><br>
                            </p>
                        </div>
                        <div class="card-footer d-flex gap-2">
                            <a href="communication.php?type=partenaire&editer=<?php echo $i; ?>&type_liste=partenaire"
                            class="btn btn-outline-primary btn-sm">✏️ Modifier</a>
                            <form method="POST" action="communication.php?type=partenaire"
                                onsubmit="return confirm('Supprimer ce partenaire ?')">
                                <input type="hidden" name="action"     value="supprimer">
                                <input type="hidden" name="type_liste" value="partenaire">
                                <input type="hidden" name="index"      value="<?php echo $i; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">🗑️ Supprimer</button>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
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
        foreach ($annonces as $i => $a):
            $enEdition = ($editerIndex === $i && $editerType === 'client');
        ?>
            <div class="col">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span><?php echo htmlspecialchars(ucfirst($a['entreprise'])); ?></span>
                        <img src="<?php echo htmlspecialchars($a['logo']); ?>"
                            alt="Logo" class="img-fluid" style="max-height:50px;">
                    </div>

                    <?php if ($enEdition): ?>
                        <div class="card-body">
                            <form method="POST" action="communication.php" enctype="multipart/form-data">
                                <input type="hidden" name="action"     value="modifier">
                                <input type="hidden" name="type_liste" value="client">
                                <input type="hidden" name="index"      value="<?php echo $i; ?>">
                                <div class="mb-2">
                                    <label class="form-label">Nom</label>
                                    <input type="text" class="form-control form-control-sm" name="nom"
                                        value="<?php echo htmlspecialchars($a['nom']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Entreprise</label>
                                    <input type="text" class="form-control form-control-sm" name="entreprise"
                                        value="<?php echo htmlspecialchars($a['entreprise']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control form-control-sm" name="email"
                                        value="<?php echo htmlspecialchars($a['email']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control form-control-sm" name="telephone"
                                        value="<?php echo htmlspecialchars($a['telephone']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Précision</label>
                                    <textarea class="form-control form-control-sm" rows="3"
                                            name="detail"><?php echo htmlspecialchars($a['detail']); ?></textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Nouveau logo (optionnel)</label>
                                    <input type="file" class="form-control form-control-sm" name="logo"
                                        accept="image/png, image/jpeg">
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button type="submit" class="btn btn-success btn-sm">💾 Sauvegarder</button>
                                    <a href="communication.php" class="btn btn-outline-secondary btn-sm">Annuler</a>
                                </div>
                            </form>
                        </div>

                    <?php else: ?>
                        <div class="card-body">
                            <p class="card-text">
                                <strong>Nom :</strong> <?php echo htmlspecialchars($a['nom']); ?><br>
                                <strong>Email :</strong> <?php echo htmlspecialchars($a['email']); ?><br>
                                <strong>Contact :</strong> <?php echo htmlspecialchars($a['telephone']); ?><br>
                                <strong>Précision :</strong> <?php echo htmlspecialchars($a['detail']); ?><br>
                            </p>
                        </div>
                        <div class="card-footer d-flex gap-2">
                            <a href="communication.php?editer=<?php echo $i; ?>&type_liste=client"
                            class="btn btn-outline-primary btn-sm">✏️ Modifier</a>
                            <form method="POST" action="communication.php"
                                onsubmit="return confirm('Supprimer ce client ?')">
                                <input type="hidden" name="action"     value="supprimer">
                                <input type="hidden" name="type_liste" value="client">
                                <input type="hidden" name="index"      value="<?php echo $i; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">🗑️ Supprimer</button>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
        </div>
        <h2 class="text-center mb-4" style="color:#1D9E75;">Liste partenaires</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
        <?php
        $annonces = json_decode(file_get_contents("./data/annuaire_commpar.json"), true);
        foreach ($annonces as $i => $a):
            $enEdition = ($editerIndex === $i && $editerType === 'partenaire');
        ?>
            <div class="col">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span><?php echo htmlspecialchars(ucfirst($a['nom'])); ?></span>
                        <img src="<?php echo htmlspecialchars($a['logo']); ?>"
                            alt="Logo" class="img-fluid" style="max-height:50px;">
                    </div>

                    <?php if ($enEdition): ?>
                        <div class="card-body">
                            <form method="POST" action="communication.php" enctype="multipart/form-data">
                                <input type="hidden" name="action"     value="modifier">
                                <input type="hidden" name="type_liste" value="partenaire">
                                <input type="hidden" name="index"      value="<?php echo $i; ?>">
                                <div class="mb-2">
                                    <label class="form-label">Entreprise</label>
                                    <input type="text" class="form-control form-control-sm" name="nom"
                                        value="<?php echo htmlspecialchars($a['nom']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Secteur</label>
                                    <input type="text" class="form-control form-control-sm" name="secteur"
                                        value="<?php echo htmlspecialchars($a['secteur']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Contact</label>
                                    <input type="text" class="form-control form-control-sm" name="contact"
                                        value="<?php echo htmlspecialchars($a['contact']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control form-control-sm" name="email"
                                        value="<?php echo htmlspecialchars($a['email']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control form-control-sm" name="telephone"
                                        value="<?php echo htmlspecialchars($a['telephone']); ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Précision</label>
                                    <textarea class="form-control form-control-sm" rows="3"
                                            name="detail"><?php echo htmlspecialchars($a['detail']); ?></textarea>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Nouveau logo (optionnel)</label>
                                    <input type="file" class="form-control form-control-sm" name="logo"
                                        accept="image/png, image/jpeg">
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button type="submit" class="btn btn-success btn-sm">💾 Sauvegarder</button>
                                    <a href="communication.php" class="btn btn-outline-secondary btn-sm">Annuler</a>
                                </div>
                            </form>
                        </div>

                    <?php else: ?>
                        <div class="card-body">
                            <p class="card-text">
                                <strong>Contact :</strong> <?php echo htmlspecialchars($a['contact']); ?><br>
                                <strong>Secteur :</strong> <?php echo htmlspecialchars($a['secteur']); ?><br>
                                <strong>Email :</strong> <?php echo htmlspecialchars($a['email']); ?><br>
                                <strong>Téléphone :</strong> <?php echo htmlspecialchars($a['telephone']); ?><br>
                                <strong>Précision :</strong> <?php echo htmlspecialchars($a['detail']); ?><br>
                            </p>
                        </div>
                        <div class="card-footer d-flex gap-2">
                            <a href="communication.php?editer=<?php echo $i; ?>&type_liste=partenaire"
                            class="btn btn-outline-primary btn-sm">✏️ Modifier</a>
                            <form method="POST" action="communication.php"
                                onsubmit="return confirm('Supprimer ce partenaire ?')">
                                <input type="hidden" name="action"     value="supprimer">
                                <input type="hidden" name="type_liste" value="partenaire">
                                <input type="hidden" name="index"      value="<?php echo $i; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">🗑️ Supprimer</button>
                            </form>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </section>
    <?php piedpage(); ?>
</body>
</html>