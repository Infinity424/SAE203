<?php
/*
Plugin Name: Temoignages Clients
Description: Un plugin de pour gerer les temoignages des clients
Version: 1.0
Author: Techlok
*/
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
register_activation_hook( __FILE__, 'wp_td_todo_create_table' );
function wp_td_todo_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'td_todos';
    $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        task_name text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
add_action( 'admin_menu', 'wp_td_todo_add_menu' );
function wp_td_todo_add_menu() {
    add_menu_page(
        'Ma Todo Liste', // Titre de la page (balise <title>)
        'Todo Liste', // Titre dans le menu latéral
        'manage_options', // Droits requis (Administrateur)
        'wp-td-todo', // Slug du menu (URL)
        'wp_td_todo_render_page', // Fonction d'affichage appelée
        'dashicons-list-view', // Icône WP (Dashicons)
        20 // Position dans le menu
    );
}
function wp_td_todo_render_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'td_todos';
    echo '<div class="wrap"><h1>Ma Todo Liste</h1>';
    // 1. TRAITEMENT DU FORMULAIRE
    if ( isset( $_POST['new_task'] ) && ! empty( $_POST['new_task'] ) ) {
        $task = sanitize_text_field( $_POST['new_task'] ); // Sécurité
        $wpdb->insert( $table_name, array( 'task_name' => $task ) );
        echo '<div class="notice notice-success"><p>Tâche ajoutée !</p></div>';
    }
    // 2. LE FORMULAIRE HTML
    ?>
    <form method="POST" action="">
        <input name="new_task" type="text" class="regular-text" required>
        <?php submit_button( 'Ajouter', 'primary', 'submit', false ); ?>
    </form>
    <hr>
    <?php
    $tasks = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" );
    if ( $tasks ) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Tâche</th><th>Date</th><th>Suppression</th></tr></thead>';
        echo '<tbody>';
        foreach ( $tasks as $task ) {
            echo '<tr>';
            echo '<td>' . intval( $task->id ) . '</td>';
            // Toujours échapper les données à l'affichage !
            echo '<td><strong>' . esc_html( $task->task_name ) . '</strong></td>';
            echo '<td>' . esc_html( $task->created_at ) . '</td>';?>
            <td> <form method="POST" action="">
                <input name="delete_task" type="submit" value="X" />
            </form></td>
            <?php echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>Aucune tâche pour le moment.</p>';
    }
    echo '</div>'; // Fin de la div wrap
}
add_shortcode( 'td_todo_list', 'wp_td_todo_display_shortcode' );
function wp_td_todo_display_shortcode() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'td_todos';
    $tasks = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" );
    ob_start(); // Démarre la temporisation
    echo '<div class="wp-td-todo-frontend"><h3>Nos objectifs :</h3>';
    if ( $tasks ) {
        echo '<ul>';
        foreach ( $tasks as $task ) {
            echo '<li>' . esc_html( $task->task_name ) . '</li>';
    }
    echo '</ul>';
    } else {
        echo '<p>Toutes les tâches sont terminées !</p>';
    }
    echo '</div>';
    return ob_get_clean(); // Retourne le contenu mis en cache
}
