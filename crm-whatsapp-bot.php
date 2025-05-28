<?php
/**
 * Plugin Name: Crm WhatsApp Bot
 * Description: Plugin de CRM para WordPress que se integra con WhatsApp utilizando Evolution API, Socket.IO y Modelos de IA.
 * Version: 1.0.1
 * Author: Percy Alvarez
 */

// Función para encolar los estilos CSS y el JavaScript
function crm_whatsapp_bot_enqueue_scripts( $hook ) {
    // Solo encolar los scripts y estilos en la página del plugin
    if ( 'toplevel_page_crm-whatsapp-bot' != $hook) {
        return;
    }

    wp_enqueue_media();
    add_thickbox();

    wp_enqueue_style( 'crm-css', plugin_dir_url( __FILE__ ) . 'css/crm.css' );
    wp_enqueue_script( 'crm-js', plugin_dir_url( __FILE__ ) . 'js/crm.js', array( 'jquery', 'intl-tel-input-js' ), '1.0', true );
    wp_enqueue_script( 'socket-js', plugin_dir_url( __FILE__ ) . 'js/socket.js', array( 'jquery', 'socket-io' ), '1.0', true );
    wp_enqueue_script( 'socket-io', esc_url( get_option( 'socket_io_url' ) ) . '/socket.io/socket.io.js', array('jquery'), '4.8.1', true );
    wp_localize_script( 'socket-js', 'crm_whatsapp_bot_params', array(
        'socketIoUrl' => esc_url( get_option( 'socket_io_url' ) ),
        'socketIoRoom' => esc_attr( get_option( 'socket_io_room', 'crm-whatsapp-bot' ) )
    ));

    // SweetAlert
    wp_enqueue_style( 'sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css', array(), '11.0.18' );
    wp_enqueue_script( 'sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.js', array( 'jquery' ), '11.0.18', true );

    // intl-tel-input
    wp_enqueue_style( 'intl-tel-input-css', 'https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/css/intlTelInput.css' );
    wp_enqueue_script( 'intl-tel-input-js', 'https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/js/intlTelInput.min.js', array( 'jquery' ), '25.3.1', true );
}
add_action( 'admin_enqueue_scripts', 'crm_whatsapp_bot_enqueue_scripts' );

// Incluir el archivo de configuración
include_once plugin_dir_path(__FILE__) . 'settings.php';
require_once plugin_dir_path( __FILE__ ) . 'api.php';
require_once plugin_dir_path( __FILE__ ) . 'crm.php';

// Añadir enlace de configuración en la lista de plugins
function crm_whatsapp_bot_plugin_action_links( $links ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=crm-whatsapp-bot-settings' ) . '">' . __( 'Settings', 'crm-whatsapp-bot' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'crm_whatsapp_bot_plugin_action_links' );


function crm_whatsapp_bot_show_user_avatar( $avatar, $id_or_email, $args ) {
    $user = false;

    if ( is_numeric( $id_or_email ) ) {
        $id = (int) $id_or_email;
        $user = get_userdata( $id );
    } elseif ( is_object( $id_or_email ) && isset( $id_or_email->user_id ) ) {
        $id = (int) $id_or_email->user_id;
        $user = get_userdata( $id );
    } elseif ( is_email( $id_or_email ) ) {
        $user = get_user_by( 'email', $id_or_email );
    }

    if ( $user && is_object( $user ) ) {
        $avatar_id = get_user_meta( $user->ID, 'wp_user_avatar', true );
        if ( $avatar_id ) {
            $avatar_url = wp_get_attachment_image_src( $avatar_id, 'thumbnail' );
            if ( $avatar_url ) {
                    // $avatar = '<img src="' . esc_url($avatar_url[0]). '" />';
                    // $avatar = '<img src="' . esc_url( $avatar_url[0] ) . 'width=80' . 'height=80' . '" alt="' . esc_attr( $user->display_name ) . '" class="avatar avatar-' . esc_attr( $args['size'] ) . ' photo" />';
                }
        }
    }

    return $avatar;
}
add_filter( 'get_avatar', 'crm_whatsapp_bot_show_user_avatar', 10, 3 );


function remove_footer_version() {
    return '';
}
add_filter( 'update_footer', 'remove_footer_version', 11 );

function remove_footer_text() {
    return '';
}
add_filter( 'admin_footer_text', 'remove_footer_text', 11 );
