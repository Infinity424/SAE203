<?php
/*
Plugin Name: Affichage Partenaires
Description: Un plugin de pour voir les Partenaires 
Version: 1.0
Author: Techlok
*/

add_shortcode( 'Affiche_Partenaire', 'wp_temoignages_display_shortcode' );

function wp_temoignages_display_shortcode() {
    wp_enqueue_style(
        'bootstrap-5',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        array(),
        '5.3.3'
    );

    wp_enqueue_script(
        'bootstrap-5-js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
        array(),
        '5.3.3',
        true
    );
    wp_add_inline_style('bootstrap-5', '
    body {
        background-color: rgb(11, 21, 38) !important;
        color : rgb(255, 255, 255) !important;
    }
    ');
    //j'ai pas réusi a fair en sorte que bootstrap ne change pas le texte et le background ducoup j'ai forcer les couleur qui on été utilisé (pas la meilleur solution mais j'ai pas réusi a trouver mieux)
    $fichier  = '../intranet/data/annuaire_commpar.json';
    $partenaires = json_decode(file_get_contents($fichier), true);
    ob_start(); // Démarre la temporisation
    ?>
    <h2 class="text-center mb-4" style="color:#1D9E75;">Liste partenaires</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
        <?php
        foreach ($partenaires as $partenaire):
        ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <span><?php echo htmlspecialchars(ucfirst($partenaire['nom'])); ?></span>
                        <img src="<?php echo htmlspecialchars($partenaire['logo']); ?>" alt="Logo" class="img-fluid mb-2" style="max-height:80px;">
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <strong>Contact :</strong> <?php echo htmlspecialchars($partenaire['contact']); ?><br>
                            <strong>Secteur :</strong> <?php echo htmlspecialchars($partenaire['secteur']); ?><br>
                            <strong>Email :</strong> <?php echo htmlspecialchars($partenaire['email']); ?><br>
                            <strong>Téléphone :</strong> <?php echo htmlspecialchars($partenaire['telephone']); ?><br>
                            <strong>Précision :</strong> <?php echo htmlspecialchars($partenaire['detail']); ?><br>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php return ob_get_clean(); // Retourne le contenu mis en cache
}
?>
