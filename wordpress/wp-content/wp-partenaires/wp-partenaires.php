<?php
function affiche_partenaire(){
    $json_url = 'http://172.18.203.208/intranet/data/annuaire_partenaires.json';
    $response = wp_remote_get($json_url);

    if(is_wp_error($response)){
        return 'Erreur lors du chargement de la page !';
    }

    $contenu = file_get_contents($json_url);
    $partenaires = json_decode($contenu , true);

    $html = '<div class="partenaires-grid">';

    foreach($partenaires as $partenaire){
        $logo = esc_html($partenaire['logo']);
        $description = esc_html($partenaire['descritption']);

        $html .= '<div class="partenaire-item">';
        $html .= '<img src = "' . $logo . '" alt="Logo de ' . $partenaire['nom'] . '" class="partenaire-logo"> ';
        $html .= '<p>"' . $description .'"</p> ';

        $html .= '</div>';
        return

        shortcode_atts('partenaires', 'affiche_partenaire');


    }
        

}

?>