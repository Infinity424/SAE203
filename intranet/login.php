<?php
session_start();
$page_title = 'Connexion';

// Simulation connexion (à remplacer par lecture JSON users.json)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp   = $_POST['password'] ?? '';

    // Exemple : lire users.json
    $users_file = __DIR__ . '/data/users.json';
    if (file_exists($users_file)) {
        $users = json_decode(file_get_contents($users_file), true) ?? [];
        foreach ($users as $u) {
            if ($u['login'] === $login && password_verify($mdp, $u['password'])) {
                $_SESSION['user'] = $u;
                header('Location: index.php');
                exit;
            }
        }
    }
    $error = 'Identifiant ou mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — TechLoc</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100">

<div class="col-11 col-sm-8 col-md-5 col-lg-4 col-xl-3">

    <div class="text-center mb-4">
        <div class="fs-3 fw-bold">
            <span class="text-white">TECH</span><span class="text-green">LOC</span>
        </div>
        <p class="text-muted small mt-1">Portail de connexion — réservé à l'équipe interne</p>
    </div>

    <div class="card p-4">
        <h5 class="text-white fw-semibold mb-4">
            <i class="bi bi-lock-fill text-green me-2"></i>Connexion
        </h5>

        <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label small">Identifiant</label>
                <input type="text" name="login" class="form-control" placeholder="votre.identifiant" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label small">Mot de passe</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-green w-100 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
            </button>
        </form>
    </div>

    <p class="text-center text-muted small mt-3">
        &copy; <?php echo date('Y'); ?> TechLoc
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
