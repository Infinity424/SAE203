<?php
/*
Plugin Name: Temoignages Clients
Description: Un plugin de pour gerer les temoignages des clients
Version: 1.0
Author: Techlok
*/

//if ( ! defined( 'ABSPATH' ) ) {
//exit;
//}
//register_activation_hook( __FILE__, 'wp_temoignages_create_table' );
//function wp_temoignages_create_table() {
//    $FileName = "../../intranet/data/temoignages.json";
//    touch($FileName);
//
//    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//
//}
add_action( 'admin_menu', 'wp_temoignages_add_menu' );

function wp_temoignages_add_menu() {
    add_menu_page(
        'Gestion des témoignages', // Titre de la page (balise <title>)
        'Gestion des témoignages', // Titre dans le menu latéral
        'manage_options', // Droits requis (Administrateur)
        'wp-temoignages', // Slug du menu (URL)
        'wp_temoignages_render_page', // Fonction d'affichage appelée
        'dashicons-list-view', // Icône WP (Dashicons)
        20 // Position dans le menu
    );
}
function wp_temoignages_render_page() {
    echo '<div class="wrap"><h1>Gestion des témoignages</h1>';
    // 1. TRAITEMENT DU FORMULAIRE
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'Ajouter'){
        if ( isset( $_POST['nom'] ) && ! empty( $_POST['nom'] ) && isset( $_POST['entreprise'] ) && ! empty( $_POST['entreprise'] ) && isset( $_POST['citation'] ) && ! empty( $_POST['citation'] ) && isset( $_POST['note'] ) && ! empty( $_POST['note'] ) ) {
            $nom = sanitize_text_field(
                wp_unslash($_POST['nom'])//pour éviter avoir plusieur \ de sauvegarder causant des problème par la suite
            );

            $entreprise = sanitize_text_field(
                wp_unslash($_POST['entreprise'])
            );

            $citation = sanitize_textarea_field(
                wp_unslash($_POST['citation'])
            );
            $note = max( 1, min( 5, intval( $_POST['note'] ) ) );
            
            $fichier  = '../../intranet/data/temoignages.json';
            $temoignages = json_decode(file_get_contents($fichier), true);
            
            $nouveautemoignages = [
                "id"            => uniqid('tem_', true), //code pour généré un id unique
                "Nom"           => $nom,
                "Entreprise"    => $entreprise,
                "Citation"      => $citation,
                "Note"          => $note,
                "ACT"           => "Activer",
                ];
                
            $temoignages[] = $nouveautemoignages;

            file_put_contents($fichier, json_encode($temoignages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo '<div class="notice notice-success"><p>Temoignages ajoutée !</p></div>';
        }
        else{
            echo "<div class='notice'><p>Problème lors de l'ajout du témoignage</p></div>";
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
        $fichier  = '../../intranet/data/temoignages.json';
        $temoignages = json_decode(file_get_contents($fichier), true);
        $cible = trim($_POST['id'] ?? '');

        if ($cible === '') {
            $message = "Temoignage cible manquant.";
            $typeMsg = "danger";
        } else {
            $nb_avant = count($temoignages);
            $temoignages    = array_values(array_filter($temoignages, fn($temoignage) => $temoignage['id'] !== $cible));

            if (count($temoignages) === $nb_avant) {
                $message = "id introuvable.";
                $typeMsg = "warning";
            } else {
                $resultat = file_put_contents($fichier, json_encode($temoignages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                if ($resultat === false) {
                    $message = "Erreur lors de la sauvegarde.";
                    $typeMsg = "danger";
                } else {
                    $message = "Temoignage supprimé.";
                    $typeMsg = "success";
                }
            }
        }
        echo "<div class='notice notice-$typeMsg'><p>$message</p></div>";
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {

    $fichier = '../../intranet/data/temoignages.json';
    $temoignages = json_decode(file_get_contents($fichier), true);

    $id = $_POST['id'];

    foreach ($temoignages as &$temoignage) {

        if ($temoignage['id'] === $id) {
            $temoignage['Nom'] = sanitize_text_field(wp_unslash($_POST['nom']));
            $temoignage['Entreprise'] = sanitize_text_field(wp_unslash($_POST['entreprise']));
            $temoignage['Citation'] = sanitize_textarea_field(wp_unslash($_POST['citation']));
            $temoignage['Note'] = max( 1, min( 5, intval( $_POST['note'] ) ) );
            $temoignage['ACT'] = sanitize_text_field($_POST['ACT']);
            break;
        }
    }

    file_put_contents( $fichier, json_encode($temoignages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) );

    echo '<div class="notice notice-success"><p>Témoignage modifié !</p></div>';
}
    // 2. LE FORMULAIRE HTML
    // Suppression d'un utilisateur
    ?>
    <form method="POST" action=""> 
        <label for="fname">Nom :</label><br>
        <input id="fname" name="nom" type="text" class="regular-text" required><br>
        
        <label for="fentreprise">Entreprise :</label><br>
        <input id="fentreprise" name="entreprise" type="text" class="regular-text" required><br>
        

        <label for="fcitation" class="form-label">Citation</label><br>
        <textarea class="form-control" id="fcitation" name="citation" rows="3" placeholder="..." required></textarea>
        
        <div>
            <input type="radio" id="etoile_1" name="note" value="1" />
            <label for="etoile_1">1 ★</label>

            <input type="radio" id="etoile_2" name="note" value="2" />
            <label for="etoile_2">2 ★</label>

            <input type="radio" id="etoile_3" name="note" value="3" />
            <label for="etoile_3">3 ★</label>

            <input type="radio" id="etoile_4" name="note" value="4" />
            <label for="etoile_4">4 ★</label>

            <input type="radio" id="etoile_5" name="note" value="5" checked />
            <label for="etoile_5">5 ★</label>
        </div>
        <?php submit_button( 'Ajouter', 'primary', 'action', false,['value' => 'ajouter']); ?>
    </form>
    <hr>
    <?php
    $fichier  = '../../intranet/data/temoignages.json';
    $temoignages = json_decode(file_get_contents($fichier), true);
    if ( $temoignages ) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Nom</th><th>Entreprise</th><th>Citation</th><th>Note</th><th>Activer?</th><th>Modification</th></tr></thead>';
        echo '<tbody>';
        foreach ($temoignages as $temoignage) {
        ?>
        <tr>
            <form method="POST" action="">
                <td>
                    <input
                        type="text"
                        name="nom"
                        value="<?php echo esc_attr($temoignage['Nom']); ?>"
                    >
                </td>

                <td>
                    <input
                        type="text"
                        name="entreprise"
                        value="<?php echo esc_attr($temoignage['Entreprise']); ?>"
                    >
                </td>

                <td>
                    <textarea
                        name="citation"
                        rows="2"
                    ><?php echo esc_textarea($temoignage['Citation']); ?></textarea>
                </td>

                <td>
                    <input
                        type="number"
                        name="note"
                        min="1"
                        max="5"
                        value="<?php echo esc_attr($temoignage['Note']); ?>"
                    >
                </td>

                <td>
                    <select name="ACT">
                                    <option value=Activer <?php if ($temoignage['ACT'] === "Activer") echo 'selected'; ?>>
                                        Activer
                                    </option>
                                    <option value=Desactiver <?php if ($temoignage['ACT'] === "Desactiver") echo 'selected'; ?>>
                                        Désactiver
                                    </option>
                            </select>
                </td>
                
                <td>
                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo esc_attr($temoignage['id']); ?>"
                    >

                    <button
                        type="submit"
                        name="action"
                        value="modifier"
                        class="button button-primary"
                    >
                        Enregistrer
                    </button>

                    <button
                        type="submit"
                        name="action"
                        value="supprimer"
                        class="button button-secondary"
                        onclick="return confirm('Supprimer ce témoignage ?');"
                    >
                        X
                    </button>
                </td>
            </form>
        </tr>
        <?php
    }

    echo '</tbody></table>';
    } else {
        echo '<p>Aucun témoignage pour le moment.</p>';
    }
    echo '</div>'; // Fin de la div wrap
}

add_shortcode( 'temoignages_clients', 'wp_temoignages_display_shortcode' );
//copier du cour c'est pour faire des étoile
function temoignages_afficher_note( $note ) {
    $note = intval( $note );
    $html = '<span class="temoignage-note" aria-label="Note : ' . $note . ' sur 5">';
    for ( $i = 1; $i <= 5; $i++ ) {
        $html .= ( $i <= $note ) ? '<span style="color:#ffc107;">★</span>'
                                  : '<span style="color:#dee2e6;">★</span>';
    }
    $html .= '</span>';
    return $html;
}
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
    $fichier  = '../intranet/data/temoignages.json';
    $temoignages = json_decode(file_get_contents($fichier), true);
    ob_start(); // Démarre la temporisation
    echo"<div class=\"temoignages-bootstrap\">";
    if ( $temoignages ) {
        ?>
        <h2 class="text-center mb-4" style="color:#1D9E75;">Liste Témoignage</h2>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4"> 
        <?php 
        foreach ($temoignages as $temoignage): 
        ?>    
        <?php if ($temoignage['ACT'] === "Activer"): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <span><?php echo htmlspecialchars(ucfirst($temoignage['Entreprise'])); ?></span>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            <strong>Nom :</strong> <?php echo htmlspecialchars($temoignage['Nom']); ?><br>
                            <strong>Note :</strong><br>
                            <?php echo '    <p class="mb-1">' . temoignages_afficher_note($temoignage['Note']) . '</p>';?>
                            <strong>Citation :</strong> <?php echo htmlspecialchars($temoignage['Citation']); ?><br>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif ?>
        <?php endforeach; ?>
        </div>
        <?php
    } else {
        echo '<p>Aucun témoignage pour le moment.</p>';
    }
    echo '</div>'; // Fin de la div wrap
    return ob_get_clean(); // Retourne le contenu mis en cache
}
?>