<?php
function affiche_partenaire(){
    $json_url = 'http://172.18.203.208/intranet/data/annuaire_partenaires.json';
    $response = wp_remote_get($json_url);

    if(is_wp_error($response)){
        return 'Erreur lors du chargement de la page !';
    }

    $donne = file_get_contents($json_url);
    $tableau = json_decode($donne , true);
}




?>