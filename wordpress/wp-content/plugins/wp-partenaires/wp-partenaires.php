<?php
/*
Plugin Name: Partenaires
Description: Un plugin de pour gerer les partenaires
Version: 1.0
Author: Techlok
*/

add_shortcode('partenaires', 'affiche_partenaire');

function affiche_partenaire(){
    $json_url = '../../intranet/data/annuaire_partenaires.json';
    $response = wp_remote_get($json_url);

    if(!file_exists($json_url)){
        return 'Erreur lors du chargement de la page !';
    }

    $contenu = file_get_contents($json_url);
    $partenaires = json_decode($contenu , true);

    if(!is_array($partenaires)){
        return 'Erreur lors de la lecture des partenaires !';
    }
    ob_start(); // Démarre la temporisation

    ?>


  <div class="partenaires-grid">'

    <?php foreach($partenaires as $partenaire) :
        $logo = esc_html($partenaire['logo']);
        $description = esc_html($partenaire['descritption']);
        $nom = esc_html($partenaire['nom']);
        ?>

        <div class="partenaire-item">'
        <img src = " <?php echo $logo; ?>" alt="Logo de <?php echo $nom; ?>" class="partenaire-logo"> ';
        <p>"<?php echo $description; ?>"</p> 
        </div>
    <?php endforeach; ?>
    </div>

    <?php return ob_get_clean();

}
?>
