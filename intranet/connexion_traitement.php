<?php
session_start();

// Rediriger si déjà connecté
if (isset($_SESSION['utilisateur'])) {
    header("Location: ./accueil.php");
    exit();
}

// Vérifier que la requête vient bien d'un POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ./connexion.php");
    exit();
}

$utilisateur_saisi = trim($_POST['utilisateur'] ?? '');
$motdepasse_saisi  = $_POST['motdepasse'] ?? '';

// Validation des entrées
if ($utilisateur_saisi === '' || $motdepasse_saisi === '') {
    header("Location: ./connexion.php?erreur=champs_vides");
    exit();
}

// Lecture du fichier JSON
$fichier = './data/SAE203-utilisateurs.json';
if (!file_exists($fichier)) {
    header("Location: ./connexion.php?erreur=serveur");
    exit();
}

$contenu = file_get_contents($fichier);
$json    = json_decode($contenu, true);

if (!is_array($json)) {
    header("Location: ./connexion.php?erreur=serveur");
    exit();
}

// Recherche de l'utilisateur et vérification du mot de passe
$utilisateurConnecte = null;
foreach ($json as $user) {
    if (isset($user['utilisateur']) && $user['utilisateur'] === $utilisateur_saisi) {
        if (isset($user['motdepasse']) && password_verify($motdepasse_saisi, $user['motdepasse'])) {
            $utilisateurConnecte = $user;
            break;
        }
        break;
    }
}

if ($utilisateurConnecte === null) {
    header("Location: ./connexion.php?erreur=identifiants");
    exit();
}

// Regénérer l'ID de session pour prévenir la fixation de session
session_regenerate_id(true);

// Normalisation du champ "role" : toujours stocker un tableau en session
$roleJSON = $utilisateurConnecte['role'] ?? 'salarié';
if (is_array($roleJSON)) {
    $roles = array_values(array_unique(array_filter(array_map('trim', $roleJSON))));
} else {
    $roles = array_values(array_unique(array_filter(array_map('trim', explode(',', $roleJSON)))));
}

// Création des variables de session
$_SESSION['utilisateur'] = $utilisateurConnecte['utilisateur'];
$_SESSION['role']        = $roles;   // <= tableau
$_SESSION['email']       = $utilisateurConnecte['email'] ?? '';

header("Location: ./accueil.php");
exit();