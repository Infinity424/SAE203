<?php
function getRoles(): array {
    $role = $_SESSION['role'] ?? [];
    if (is_string($role)) {
        return array_filter(array_map('trim', explode(',', $role)));
    }
    return is_array($role) ? $role : [];
}

/**
 * Vérifie si l'utilisateur possède AU MOINS un des rôles donnés.
 * hasRole('admin')           => true si admin est dans ses rôles
 * hasRole('admin', 'finance') => true si l'un OU l'autre
 */
function hasRole(string ...$cherches): bool {
    $roles = getRoles();
    foreach ($cherches as $r) {
        if (in_array($r, $roles, true)) return true;
    }
    return false;
}

/**
 * Retourne true si l'utilisateur est connecté.
 */
function estConnecte(): bool {
    return isset($_SESSION['utilisateur']);
}

//  EN-TÊTE HTML
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

//  NAVIGATION
function navigation($active, $racine) {

    // Droits par rôle :
    // Chaque rôle déclare les clés de pages auxquelles il donne accès.
    $droitsParRole = [
        'admin'   => ['accueil','profil','inscription','administration','communication','finance',
                      'annuaire_clients','annuaire_entreprise','annuaire_partenaires'],
        'manager' => ['accueil','profil','inscription','communication','finance',
                      'annuaire_clients','annuaire_entreprise','annuaire_partenaires'],
        'modo'    => ['accueil','profil','communication','finance',
                      'annuaire_clients','annuaire_entreprise','annuaire_partenaires'],
        'com'     => ['accueil','profil','communication',
                      'annuaire_clients','annuaire_entreprise','annuaire_partenaires'],
        'finance' => ['accueil','profil','finance',
                      'annuaire_clients','annuaire_entreprise','annuaire_partenaires'],
        'salarié' => ['accueil','profil',
                      'annuaire_clients','annuaire_entreprise','annuaire_partenaires'],
    ];

    // Définition de TOUTES les pages (ordre d'affichage)
    $toutesPagesOrdre = [
        'accueil'              => ['url' => $racine . '/accueil.php',              'label' => 'Accueil'],
        'profil'               => ['url' => $racine . '/profil.php',               'label' => 'Mon profil'],
        'inscription'          => ['url' => $racine . '/inscription.php',          'label' => 'Inscription'],
        'administration'       => ['url' => $racine . '/administration.php',       'label' => 'Administration'],
        'communication'        => ['url' => $racine . '/communication.php',        'label' => 'Communication'],
        'finance'              => ['url' => $racine . '/finance.php',              'label' => 'Finance'],
        'annuaire_clients'     => ['url' => $racine . '/annuaire_clients.php',     'label' => 'Annuaire clients'],
        'annuaire_entreprise'  => ['url' => $racine . '/annuaire_entreprise.php',  'label' => 'Annuaire entreprise'],
        'annuaire_partenaires' => ['url' => $racine . '/annuaire_partenaires.php', 'label' => 'Annuaire partenaires'],
    ];

    // Calcul de l'union des pages accessibles
    $pagesAutorisees = [];
    if (estConnecte()) {
        foreach (getRoles() as $role) {
            if (isset($droitsParRole[$role])) {
                foreach ($droitsParRole[$role] as $cle) {
                    $pagesAutorisees[$cle] = true;
                }
            }
        }
    } else {
        $pagesAutorisees['accueil'] = true;
    }

    // Construction du menu dans l'ordre déclaré
    $liens = [];
    foreach ($toutesPagesOrdre as $cle => $info) {
        if (isset($pagesAutorisees[$cle])) {
            $liens[$cle] = $info;
        }
    }

    // Rendu HTML
    echo '<nav class="navbar navbar-expand-sm mb-4 shadow-sm justify-content-center" style="background-color:#0B1526;">';
    echo '<div class="container-fluid">';
    echo '<img class="rounded" src="img/logo.png" width="80" height="80" alt="Logo Techloc">';
    echo '<a class="navbar-brand fw-bold" style="color:#1D9E75;" href="' . $racine . '/accueil.php">TechLoc</a>';
    echo '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">';
    echo '<span class="navbar-toggler-icon"></span></button>';
    echo '<div class="collapse navbar-collapse" id="navbarNav">';
    echo '<ul class="navbar-nav me-auto">';

<<<<<<< HEAD
=======
    // 1 navbarre pour chaque role
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
        $liens = [
        'accueil'                   => ['url' => $racine . '/accueil.php',                'label' => 'Accueil'],
        'profil'                    => ['url' => $racine . '/profil.php',                 'label' => 'Mon profil'],
        'inscriptions'              => ['url' => $racine . '/inscription.php',            'label' => 'Inscription'],
        'administration'            => ['url' => $racine . '/administration.php',         'label' => 'Administration'],
        'communication'             => ['url' => $racine . '/communication.php',          'label' => 'Communication'],
        'finance'                   => ['url' => $racine . '/finance.php',                'label' => 'Finance'],
        ];
    }elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'modo') {
        $liens = [
        'accueil'                   => ['url' => $racine . '/accueil.php',                'label' => 'Accueil'],
        'profil'                    => ['url' => $racine . '/profil.php',                 'label' => 'Mon profil'],
        'communication'             => ['url' => $racine . '/communication.php',          'label' => 'Communication'],
        'finance'                   => ['url' => $racine . '/finance.php',                'label' => 'Finance'],
        ];
    }elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'manager') {
        $liens = [
        'accueil'                   => ['url' => $racine . '/accueil.php',                'label' => 'Accueil'],
        'profil'                    => ['url' => $racine . '/profil.php',                 'label' => 'Mon profil'],
        'inscriptions'              => ['url' => $racine . '/inscription.php',            'label' => 'Inscriptions'],
        'communication'             => ['url' => $racine . '/communication.php',          'label' => 'Communication'],
        'finance'                   => ['url' => $racine . '/finance.php',                'label' => 'Finance'],
        ];
    }elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'com') {
        $liens = [
        'accueil'                   => ['url' => $racine . '/accueil.php',                'label' => 'Accueil'],
        'profil'                    => ['url' => $racine . '/profil.php',                 'label' => 'Mon profil'],
        'communication'             => ['url' => $racine . '/communication.php',          'label' => 'Communication'],
        ];
    }elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'finance') {
        $liens = [
        'accueil'                   => ['url' => $racine . '/accueil.php',                'label' => 'Accueil'],
        'profil'                    => ['url' => $racine . '/profil.php',                 'label' => 'Mon profil'],
        'finance'                   => ['url' => $racine . '/finance.php',                'label' => 'Finance'],
        ];
    }elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'salarié') {
        $liens = [  
        'accueil'                   => ['url' => $racine . '/accueil.php',                'label' => 'Accueil'],
        'profil'                    => ['url' => $racine . '/profil.php',                 'label' => 'Mon profil'],
        ];
    }else {
        $liens = [  
        'accueil'                   => ['url' => $racine . '/accueil.php',                'label' => 'Accueil'],
        ];
    }

    //Bloc pour mettre les pages de la navbarre en activé automatiquement
>>>>>>> 16bf44278d6b3d902604c5687b115546de24af15
    foreach ($liens as $key => $lien) {
        $isActive = ($active === $key) ? ' active" aria-current="page' : '';
        echo '<li class="nav-item">';
        echo '<a class="nav-link text-white ' . $isActive . '" href="' . $lien['url'] . '">' . $lien['label'] . '</a>';
        echo '</li>';
    }
    if (isset($_SESSION['role'])) {
        $annuaireActif = in_array($active, ['annuaire_clients', 'annuaire_entreprise', 'annuaire_partenaires']);
        echo '<li class="nav-item dropdown">';
        echo '<a class="nav-link dropdown-toggle text-white' . ($annuaireActif ? ' active' : '') . '" href="#" role="button" data-bs-toggle="dropdown">Annuaires</a>';
        echo '<ul class="dropdown-menu" style="background-color:#0B1526;">';
        echo '<li><a class="dropdown-item' . ($active === 'annuaire_clients'     ? ' active' : '') . '" style="color:#1D9E75;" href="' . $racine . '/annuaire_clients.php">Clients</a></li>';
        echo '<li><a class="dropdown-item' . ($active === 'annuaire_entreprise'  ? ' active' : '') . '" style="color:#1D9E75;" href="' . $racine . '/annuaire_entreprise.php">Entreprise</a></li>';
        echo '<li><a class="dropdown-item' . ($active === 'annuaire_partenaires' ? ' active' : '') . '" style="color:#1D9E75;" href="' . $racine . '/annuaire_partenaires.php">Partenaires</a></li>';
        echo '</ul>';
        echo '</li>';
    }

    echo '</ul>';
    echo '<p class="mb-1" style="color:#1D9E75;">';

    if (estConnecte()) {
        echo 'Connecté : <strong>' . htmlspecialchars($_SESSION['utilisateur']) . '</strong> ';
        // Badge pour chaque rôle
        foreach (getRoles() as $r) {
            echo '<span class="badge bg-secondary me-1">' . htmlspecialchars($r) . '</span>';
        }
        echo '<a href="' . $racine . '/deconnexion.php" class="btn btn-sm ms-2" style="color:#1D9E75; border-color:#1D9E75;">Se déconnecter</a>';
    } else {
        echo '<span style="color:#ffffff;">Visiteur anonyme</span> ';
        echo '<a href="' . $racine . '/connexion.php" class="btn btn-outline-light btn-sm ms-2" style="color:#1D9E75; border-color:#1D9E75;">Se connecter</a>';
    }

    echo '</p>';
    echo '</div></div></nav>';
}

//  PIED DE PAGE
function piedpage() {
    date_default_timezone_set('Europe/Paris');
    $date  = date("d/m/Y");
    $heure = date("H:i:s");
    $annee = date("Y");

    echo '<footer class="text-center py-4 mt-5" style="background-color:#0B1526;">';
    echo '<p class="mb-1" style="color:#1D9E75;">TechLoc &nbsp;|&nbsp; <a href="mailto:contact@techLoc.fr" style="color:#1D9E75;">contact@techLoc.fr</a> &nbsp;|&nbsp; Groupe 3</p>';
    echo '<p class="mb-1" style="color:#1D9E75;">&copy; ' . $annee . ' &nbsp;|&nbsp; ' . $date . ' &nbsp;|&nbsp; ' . $heure . '</p>';
    echo '</footer>';
}