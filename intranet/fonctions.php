<?php

// Définit l'en-tête technique (meta, title, bootstrap)
function parametrespage($titre) {
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<meta name="description" content="TechLoc">';
    echo '<meta name="author" content="Nathan Lévêque - Jean-Baptiste Aubry - Corwin Bourdet - Raphaël Bouchard - Martin Bodennec - Gwendal Michot">';
    echo '<meta name="keywords" content="Wordpress, HTML, Bootstrap, PHP">';
    echo "<title>$titre – TechLoc</title>";
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>';
    echo '<link rel="icon" href="img/logo.png">';
}


// Affiche la barre de navigation Bootstrap
function navigation($active, $racine) {
    echo '<nav class="navbar navbar-expand-sm mb-4 shadow-sm justify-content-center" style="background-color:#0B1526;">';
    echo '<div class="container-fluid">';
    echo '<img class="rounded" src="img/logo.png" width="80" height="80" alt="Logo Techloc" class="mb-2">';
    echo '<a class="navbar-brand fw-bold" style="color:#1D9E75;" href="' . $racine . '/accueil.php">TechLoc</a>';
    echo '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">';
    echo '<span class="navbar-toggler-icon"></span></button>';
    echo '<div class="collapse navbar-collapse" id="navbarNav">';
    echo '<ul class="navbar-nav me-auto">';

    // Double navBarre pour gerer en fonction 
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        $liens = [
        'accueil'       => ['url' => $racine . '/accueil.php',                'label' => 'Accueil'],
        'inscription'   => ['url' => $racine . '/inscription.php',            'label' => 'Inscription'],
        'visualiser'    => ['url' => $racine . '/visualiser.php',             'label' => 'Annonces'],   
        'proposer'      => ['url' => $racine . '/proposer.php',               'label' => 'Proposer'],
        'rechercher'    => ['url' => $racine . '/rechercher.php',             'label' => 'Rechercher'],
        'profil'        => ['url' => $racine . '/profil.php',                 'label' => 'Mon profil'],
        'modifier'      => ['url' => $racine . '/modifier.php',               'label' => 'Modifier'],
        'Wiki'          => ['url' => $racine . '/wiki.php',                   'label' => 'Wiki'],
        ];
    } else {
        $liens = [
        'accueil'       => ['url' => $racine . '/accueil.php',                'label' => 'Accueil'],
        'inscription'   => ['url' => $racine . '/inscription.php',            'label' => 'Inscription'],
        'visualiser'    => ['url' => $racine . '/visualiser.php',             'label' => 'Annonces'],   
        'proposer'      => ['url' => $racine . '/proposer.php',               'label' => 'Proposer'],
        'rechercher'    => ['url' => $racine . '/rechercher.php',             'label' => 'Rechercher'],
        'profil'        => ['url' => $racine . '/profil.php',                 'label' => 'Mon profil'],
        'modifier'      => ['url' => $racine . '/modifier.php',               'label' => 'Modifier'],
        'administration'=> ['url' => $racine . '/administration.php',         'label' => 'Administration'],
        'view1'         => ['url' => $racine . '/admin/view_utilsateurs.php', 'label' => 'View Utilisateurs'],
        'view2'         => ['url' => $racine . '/admin/view_annonces.php',    'label' => 'View Annonces'],
        'Wiki'          => ['url' => $racine . '/wiki.php',                   'label' => 'Wiki'],
        ];
    }

    //Bloc pour mettre les pages de la navbarre en activé automatiquement
    foreach ($liens as $key => $lien) {
        $isActive = ($active === $key) ? ' active" aria-current="page' : '';
        echo '<li class="nav-item">';
        echo '<a class="nav-link text-white ' . $isActive . '" href="' . $lien['url'] . '">' . $lien['label'] . '</a>';
        echo '</li>';
    }

    echo '</ul>';
    echo '<p class="mb-1">';
    if (isset($_SESSION['utilisateur'])) {
        echo 'Connecté : <strong>' . htmlspecialchars($_SESSION['utilisateur']) . '</strong>';
        echo ' <span class="badge bg-secondary">' . htmlspecialchars($_SESSION['role']) . '</span> ';
        echo '<a href="' . $racine . '/deconnexion.php" class="btn btn-sm ms-2" style="color:#1D9E75;  border-color:#1D9E75;">Se déconnecter</a>';
    } else {
        echo '<span style="color:#ffffff;">Visiteur anonyme</span> ';
        echo '<a href="' . $racine . '/connexion.php" class="btn btn-outline-light btn-sm ms-2" style="color:#1D9E75;  border-color:#1D9E75;">Se connecter</a>';
    }
    echo '</p>';
    echo '</div></div></nav>';
}

// Affiche le pied de page
function piedpage() {
    date_default_timezone_set('Europe/Paris');
    $date  = date("d/m/Y");
    $heure = date("H:i:s");
    $annee = date("Y");
    $ip    = htmlspecialchars($_SERVER['REMOTE_ADDR']);
    $port  = htmlspecialchars($_SERVER['REMOTE_PORT']);

    echo '<footer class="text-center py-4 mt-5" style="background-color:#0B1526;">';
    echo '<p class="mb-1" style="color:#1D9E75;">TechLoc &nbsp;|&nbsp; <a href="mailto:contact@techLoc.fr" style="color:#1D9E75;">contact@techLoc.fr</a> &nbsp;|&nbsp; Groupe 3</p>';
    echo '<p class="mb-1" style="color:#1D9E75;">&copy; ' . $annee . ' &nbsp;|&nbsp; ' . $date . ' &nbsp;|&nbsp; ' . $heure . '</p>';
    echo '<p class="mb-0">';
    echo '</p>';
    echo '</footer>';
}
?>