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
    echo '<link rel="icon" href="https://images.icon-icons.com/1860/PNG/512/oldcar2_118032.png">';
}

// Affiche le haut du corps de la page
function entete($racine) {
    echo '<header class="bg-dark text-white text-center py-4 mb-2">';
    echo '<h1 class="display-5 fw-bold">TechLoc</h1>';
    echo '<img src="#" width="80" height="80" alt="Logo Techloc" class="mb-2">';
    echo '<p class="mb-1">';
    if (isset($_SESSION['utilisateur'])) {
        echo 'Connecté : <strong>' . htmlspecialchars($_SESSION['utilisateur']) . '</strong>';
        echo ' <span class="badge bg-secondary">' . htmlspecialchars($_SESSION['role']) . '</span> ';
        echo '<a href="' . $racine . '/deconnexion.php" class="btn btn-outline-light btn-sm ms-2">Se déconnecter</a>';
    } else {
        echo '<span>Visiteur anonyme</span> ';
        echo '<a href="' . $racine . '/connexion.php" class="btn btn-outline-light btn-sm ms-2">Se connecter</a>';
    }
    echo '</p>';
    echo '</header>';
}

// Affiche la barre de navigation Bootstrap
function navigation($active, $racine) {
    echo '<nav class="navbar navbar-expand-sm navbar-light bg-light mb-4 shadow-sm">';
    echo '<div class="container-fluid">';
    echo '<a class="navbar-brand fw-bold" href="' . $racine . '/test.php">TechLoc</a>';
    echo '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">';
    echo '<span class="navbar-toggler-icon"></span></button>';
    echo '<div class="collapse navbar-collapse" id="navbarNav">';
    echo '<ul class="navbar-nav me-auto">';

    // Double navBarre pour gerer en fonction 
    if (!isset($_SESSION['role']) || $_SESSION['role'] == 'admin') {
        $liens = [
        'accueil'       => ['url' => $racine . '/accueil.php',                   'label' => 'Accueil'],
        'profil'        => ['url' => $racine . '/profil.php',                 'label' => 'Mon profil'],
        'inscriptions'        => ['url' => $racine . '/inscription.php',    'label' => 'Inscriptions'],
        'administration'        => ['url' => $racine . '/administration.php',    'label' => 'Administration'],
        ];
    }elseif (!isset($_SESSION['role']) || $_SESSION['role'] == 'com') {
        $liens = [
        'accueil'       => ['url' => $racine . '/accueil.php',                   'label' => 'Accueil'],
        'profil'        => ['url' => $racine . '/profil.php',                 'label' => 'Mon profil'],
        ];
    }elseif (!isset($_SESSION['role']) || $_SESSION['role'] == 'finance') {
        $liens = [
        'accueil'       => ['url' => $racine . '/accueil.php',                   'label' => 'Accueil'],
        'profil'        => ['url' => $racine . '/profil.php',                 'label' => 'Mon profil'],
        ];
    }else {
        $liens = [  
        'accueil'       => ['url' => $racine . '/accueil.php',                   'label' => 'Accueil'],
        'inscription'   => ['url' => $racine . '/inscription.php',           'label' => 'Inscription'],
        'profil'        => ['url' => $racine . '/profil.php',                 'label' => 'Mon profil'],
        ];
    }

    //Bloc pour mettre les pages de la navbarre en activé automatiquement
    foreach ($liens as $key => $lien) {
        $isActive = ($active === $key) ? ' active" aria-current="page' : '';
        echo '<li class="nav-item">';
        echo '<a class="nav-link' . $isActive . '" href="' . $lien['url'] . '">' . $lien['label'] . '</a>';
        echo '</li>';
    }

    echo '</ul>';
    // Barre de recherche (non fonctionnelle)
    echo '<form class="d-flex" role="search">';
    echo '<input class="form-control me-2" type="search" placeholder="Rechercher..." aria-label="Search" name="recherche">';
    echo '<button class="btn btn-outline-success" type="submit">Chercher</button>';
    echo '</form>';
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

    echo '<footer class="bg-dark text-white text-center py-4 mt-5">';
    echo '<p class="mb-1">TechLoc &nbsp;|&nbsp; <a href="mailto:contact@techLoc.fr" class="text-white">contact@techLoc.fr</a> &nbsp;|&nbsp; Groupe 3</p>';
    echo '<p class="mb-1">&copy; ' . $annee . ' &nbsp;|&nbsp; ' . $date . ' &nbsp;|&nbsp; ' . $heure . '</p>';
    echo '<p class="mb-0">';
    echo '</p>';
    echo '</footer>';
}
?>