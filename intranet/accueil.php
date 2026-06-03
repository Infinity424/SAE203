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
$equipements = [
    [
        "nom" => "Laptop HP ProBook",
        "cat" => "Station de travail",
        "img" => "https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&q=80&w=400",
        "specs" => ["CPU : Intel Core i7 13th Gen", "RAM : 32 Go DDR5", "Stockage : 1 To SSD NVMe", "Réseau : Wi-Fi 6E / RJ45", "OS : Ubuntu / Win 11"],
        "prix" => 45,
    ],
    [
        "nom" => "PC Portable HP",
        "cat" => "Station de travail",
        "img" => "https://static.fnac-static.com/multimedia/Images/FR/MDM/a5/33/89/25768869/1540-1/tsp20251217150443/PC-Portable-HP-15-fd1009nf-15-6-Full-HD-Intel-Core-Ultra-5-32-Go-RAM-1-To-D-Argent-naturel.jpg",
        "specs" => ["CPU : Intel Core Ultra 5", "RAM : 32 Go DDR5", "Stockage : 1 To SSD NVMe", "Réseau : Wi-Fi 6E / RJ45", "OS : Ubuntu / Win 11"],
        "prix" => 35,
    ],
    [
        "nom" => "Switch Cisco POE 48 ports",
        "cat" => "Infrastructure Réseau",
        "img" => "https://byconnect.fr/wp-content/uploads/2022/03/Switch-Cisco-POE-48-ports-1.jpg",
        "specs" => ["Ports : 48", "PoE : Oui", "Compatible IPv6 : Oui"],
        "prix" => 20,
    ],
    [
        "nom" => "Routeur Cisco RV340",
        "cat" => "Infrastructure Réseau",
        "img" => "https://www.cisco.com/c/fr_ca/support/smb/product-support/small-business/routers-340-family/jcr:content/Grid/full_4c44/Full/spotlight_592b/image.img.jpg/1661791502352.jpghttps://byconnect.fr/wp-content/uploads/2022/03/Routeur-Cisco-RV340.jpg",
        "specs" => ["Ports : 4 Gigabit Ethernet", "VPN : Oui", "Firewall : Oui"],
        "prix" => 15,
    ],
    [
    "nom" => "Firewall Fortinet FortiGate 60F",
    "cat" => "Infrastructure Réseau",
    "img" => "https://cdn8.futura-sciences.com/a1280/images/firewall.jpeg",
    "specs" => ["Débit : 1.4 Gbps Threat Protection", "Interfaces : 10x GE RJ45", "VPN : SSL et IPsec", "Sécurité : Inspection SSL"],
    "prix" => 30
],
[
    "nom" => "Borne Wi-Fi Cisco Catalyst 9115",
    "cat" => "Infrastructure Réseau",
    "img" => "https://encrypted-tbn3.gstatic.com/shopping?q=tbn:ANd9GcTt8Ljjgu4lbIYZi8InphIVJrN2zkhe0rLJ32tIXKgu7VVtFJ3b7wCX6Y8xj9aT4V8Uwa6C8YyKtLrj9w-AMjdTXCKOkfUIdrcxNz1nX_UFpvltf11JxAYEkQ",
    "specs" => ["Norme : Wi-Fi 6 (802.11ax)", "Antennes : MU-MIMO 4x4", "Gestion : Cloud ou Contrôleur", "Alimentation : PoE (802.3af)"],
    "prix" => 12
]
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php parametrespage("Accueil"); ?>
    <style>
        .flip-card { width: 100%; height: 350px; perspective: 1000px; }
        .flip-card-inner { position: relative; width: 100%; height: 100%; text-align: center; transition: transform 0.6s; transform-style: preserve-3d; cursor: pointer; }
        .flip-card:hover .flip-card-inner { transform: rotateY(180deg); }
        .flip-card-front, .flip-card-back { position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border-radius: 0.375rem; }
        .flip-card-front { background-color: #fff; color: black; }
        .flip-card-back { background-color: #2b3e50; color: white; transform: rotateY(180deg); }
        /* https://www.w3schools.com/howto/howto_css_flip_card.asp */
    </style>
</head>
<body style="background-color:#0F1E38;">
    <?php navigation("accueil", "."); ?>
    <section class="container mt-4">
        <div class="text-center text-white py-5">
            <h1 class="display-5 fw-bold" style="color:#1D9E75;">Bienvenue sur TechLoc</h1>
        </div>
    </section>
  <div class="container text-center mb-5">
        <a href="../wordpress" class="btn btn-lg fw-bold px-4 py-2" style="background-color: #1D9E75; color: white; border: none; border-radius: 8px;">
            Notre site vitrine
        </a>
    </div>
    <div class="container my-5">
        <h2 class="fw-bold text-center mb-5" style="color:#1D9E75;">Équipements Réseau & PC Disponibles</h2>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            
            <?php foreach ($equipements as $item): ?>
            <div class="col">
                <div class="flip-card">
                    <div class="flip-card-inner shadow-sm">
                        
                        <div class="flip-card-front d-flex flex-column justify-content-between p-3 border">
                            <div class="h-75 d-flex align-items-center justify-content-center">
                                <img src="<?= $item['img'] ?>" class="img-fluid rounded object-fit-contain" alt="<?= $item['nom'] ?>" style="max-height: 200px;">
                            </div>
                            <div class="border-top pt-2 text-start">
                                <h5 class="fw-bold text-dark mb-0"><?= $item['nom'] ?></h5>
                                <small class="text-muted">Catégorie : <?= $item['cat'] ?></small>
                            </div>
                        </div>
                        
                        <div class="flip-card-back d-flex flex-column justify-content-between p-4 text-start bg-dark">
                            <div>
                                <h5 class="fw-bold border-bottom pb-2" style="color:#1D9E75;">Spécifications</h5>
                                <ul class="list-unstyled font-monospace small mt-3">
                                    <?php foreach ($item['specs'] as $spec): ?>
                                        <li class="mb-2"><strong><?= explode(":", $spec)[0] ?> :</strong> <?= explode(":", $spec)[1] ?? '' ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-secondary">
                                <div>
                                    <span class="fs-5 fw-bold text-white"><?= $item['prix'] ?>€</span><span class="small text-light">/j</span>
                                </div>
                                <button class="btn btn-sm fw-bold" style="background-color: #1D9E75; color: white; border: none;" onclick="ajouterAuPanier('<?= addslashes($item['nom']) ?>', <?= $item['prix'] ?>)">
                                    🛒 Ajouter
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
    <?php piedpage(); ?>
    <script>
        function ajouterAuPanier(nomProduit, prix) {
            alert("✅ Ajouté au panier !\n\nProduit : " + nomProduit + "\nPrix : " + prix + "€ / jour");
        }
    </script>
</body>
</html>