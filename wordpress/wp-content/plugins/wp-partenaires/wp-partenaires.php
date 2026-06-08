<?php
/*
Plugin Name: Partenaires
Description: Un plugin pour gerer les partenaires
Version: 1.0
Author: Techlok
*/

add_shortcode('partenaires', 'affiche_partenaire');

function affiche_partenaire(){
    $json_url = dirname(ABSPATH) . '/intranet/data/annuaire_partenaires.json';

    if(!file_exists($json_url)){
        return 'Erreur : Le fichier annuaire_partenaires.json est introuvable.';
    }

    $contenu = file_get_contents($json_url);
    $partenaires = json_decode($contenu, true);

    if(!is_array($partenaires)){
        return 'Erreur lors de la lecture des partenaires !';
    }

    ob_start(); 
    ?>

    <div class="partenaires-grid">

        <?php foreach($partenaires as $partenaire) :
            $logo = '..' . esc_url($partenaire['logo']);
            $description = esc_html($partenaire['descritption']); 
            $nom = esc_attr($partenaire['nom']);
            ?>

            <div class="partenaire-item">
                <img src="<?php echo $logo; ?>" alt="Logo de <?php echo $nom; ?>" class="partenaire-logo">
                <p>"<?php echo $description; ?>"</p> 
            </div>
        <?php endforeach; ?>

    </div>

    <?php 
    return ob_get_clean();
}
?>