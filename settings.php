<?php

// Añadir la página de ajustes al menú de WordPress
function crm_whatsapp_bot_add_settings_page() {
    add_options_page(
        'Crm WhatsApp Bot',
        'Crm WhatsApp Bot',
        'manage_options',
        'crm-whatsapp-bot-settings',
        'crm_whatsapp_bot_render_settings_page'
    );
}
add_action('admin_menu', 'crm_whatsapp_bot_add_settings_page');


function crm_whatsapp_bot_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Crm WhatsApp Bot Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('crm_whatsapp_bot_settings_group');
            do_settings_sections('crm-whatsapp-bot-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Función para registrar la sección y campos de ajustes
function crm_whatsapp_bot_settings_init() {
    // error_log('crm_whatsapp_bot_settings_init se está ejecutando');
    // Registrar la sección de ajustes para Evolution API
    add_settings_section(
        'crm_whatsapp_bot_evolution_api_section',
        'Evolution API Settings',
        'crm_whatsapp_bot_evolution_api_section_callback',
        'crm-whatsapp-bot-settings'
    );

    // Registrar los campos de ajustes para Evolution API
    add_settings_field(
        'evolution_api_url',
        'API URL',
        'crm_whatsapp_bot_evolution_api_url_callback',
        'crm-whatsapp-bot-settings',
        'crm_whatsapp_bot_evolution_api_section'
    );
    register_setting('crm_whatsapp_bot_settings_group', 'evolution_api_url');

    add_settings_field(
        'evolution_api_key',
        'API Key',
        'crm_whatsapp_bot_evolution_api_key_callback',
        'crm-whatsapp-bot-settings',
        'crm_whatsapp_bot_evolution_api_section'
    );
    register_setting('crm_whatsapp_bot_settings_group', 'evolution_api_key');

    // Registrar la sección de ajustes para Socket.IO
    add_settings_section(
        'crm_whatsapp_bot_socket_io_section',
        'Socket.IO Settings',
        'crm_whatsapp_bot_socket_io_section_callback',
        'crm-whatsapp-bot-settings'
    );

    // Registrar los campos de ajustes para Socket.IO
    add_settings_field(
        'socket_io_url',
        'Socket.IO URL',
        'crm_whatsapp_bot_socket_io_url_callback',
        'crm-whatsapp-bot-settings',
        'crm_whatsapp_bot_socket_io_section'
    );
    register_setting('crm_whatsapp_bot_settings_group', 'socket_io_url');

    add_settings_field(
        'socket_io_room',
        'Socket.IO Room',
        'crm_whatsapp_bot_socket_io_room_callback',
        'crm-whatsapp-bot-settings',
        'crm_whatsapp_bot_socket_io_section'
    );
    register_setting('crm_whatsapp_bot_settings_group', 'socket_io_room', array(
        'default' => 'crm-whatsapp-bot',
        'sanitize_callback' => 'sanitize_text_field'
    ));

    // Registrar la sección de ajustes para OpenAI
    add_settings_section(
        'crm_whatsapp_bot_openai_section',
        'OpenAI Settings',
        'crm_whatsapp_bot_openai_section_callback',
        'crm-whatsapp-bot-settings'
    );

    // Registrar los campos de ajustes para OpenAI
    add_settings_field(
        'openai_api_url',
        'API URL',
        'crm_whatsapp_bot_openai_api_url_callback',
        'crm-whatsapp-bot-settings',
        'crm_whatsapp_bot_openai_section'
    );
    register_setting('crm_whatsapp_bot_settings_group', 'openai_api_url');

    add_settings_field(
        'openai_api_key',
        'API Key',
        'crm_whatsapp_bot_openai_api_key_callback',
        'crm-whatsapp-bot-settings',
        'crm_whatsapp_bot_openai_section'
    );
    register_setting('crm_whatsapp_bot_settings_group', 'openai_api_key');

    // Registrar la sección de ajustes para Etiquetas
    add_settings_section(
        'crm_whatsapp_bot_etiquetas_section',
        'Etiquetas Settings',
        'crm_whatsapp_bot_etiquetas_section_callback',
        'crm-whatsapp-bot-settings'
    );

    // Registrar la sección de ajustes para Etiquetas
    add_settings_section(
        'crm_whatsapp_bot_etiquetas_section',
        'Etiquetas Settings',
        'crm_whatsapp_bot_etiquetas_section_callback',
        'crm-whatsapp-bot-settings'
    );

    register_setting( 'crm_whatsapp_bot_settings_group', 'crm_whatsapp_bot_etiquetas', 'crm_whatsapp_bot_sanitize_etiquetas' );
}

// Sanitize the tag data
function crm_whatsapp_bot_sanitize_etiquetas( $input ) {
    $sanitized_input = array();

    if ( is_array( $input ) ) {
        foreach ( $input as $key => $tag ) {
            $sanitized_input[$key]['nombre'] = sanitize_text_field( $tag['nombre'] );
            $sanitized_input[$key]['color']  = sanitize_hex_color( $tag['color'] );
        }
    }

    return $sanitized_input;
}

// Callbacks para las secciones
function crm_whatsapp_bot_evolution_api_section_callback() {
    echo '<hr>';
}

function crm_whatsapp_bot_socket_io_section_callback() {
    echo '<hr>';
}

function crm_whatsapp_bot_openai_section_callback() {
    echo '<hr>';
}


// Callbacks para los campos de ajustes
function crm_whatsapp_bot_evolution_api_url_callback() {
    $value = get_option('evolution_api_url');
    echo '<input type="text" name="evolution_api_url" value="' . esc_attr($value) . '" />';
}

function crm_whatsapp_bot_evolution_api_key_callback() {
    $value = get_option('evolution_api_key');
    echo '<input type="text" name="evolution_api_key" value="' . esc_attr($value) . '" />';
}

function crm_whatsapp_bot_socket_io_url_callback() {
    $value = get_option('socket_io_url');
    echo '<input type="text" name="socket_io_url" value="' . esc_attr($value) . '" />';
}

function crm_whatsapp_bot_socket_io_room_callback() {
    $value = get_option('socket_io_room', 'crm-whatsapp-bot');
    echo '<input type="text" name="socket_io_room" value="' . esc_attr($value) . '" />';
}

function crm_whatsapp_bot_openai_api_url_callback() {
    $value = get_option('openai_api_url');
    echo '<input type="text" name="openai_api_url" value="' . esc_attr($value) . '" />';
}

function crm_whatsapp_bot_openai_api_key_callback() {
    $value = get_option('openai_api_key');
    echo '<input type="text" name="openai_api_key" value="' . esc_attr($value) . '" />';
}

// Registrar la función de inicialización de ajustes
add_action('admin_init', 'crm_whatsapp_bot_settings_init');

function crm_whatsapp_bot_etiquetas_section_callback() {
    $etiquetas = get_option( 'crm_whatsapp_bot_etiquetas', array() );

    ?>
    <table class="wp-list-table widefat fixed striped tags">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name">Nombre</th>
                <th scope="col" class="manage-column column-description">Color</th>
                <th scope="col" class="manage-column column-slug">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( ! empty( $etiquetas ) && is_array( $etiquetas ) ) {
                foreach ( $etiquetas as $key => $etiqueta ) {
                    echo '<tr>';
                    echo '<td><input type="text" name="crm_whatsapp_bot_etiquetas[' . $key . '][nombre]" value="' . esc_attr( $etiqueta['nombre'] ) . '" /></td>';
                    echo '<td><input type="color" name="crm_whatsapp_bot_etiquetas[' . $key . '][color]" value="' . esc_attr( $etiqueta['color'] ) . '" /></td>';
                    echo '<td><button type="button" class="eliminar_etiqueta">Eliminar</button></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="3">No hay etiquetas guardadas.</td></tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th scope="col" class="manage-column column-name">Nombre</th>
                <th scope="col" class="manage-column column-description">Color</th>
                <th scope="col" class="manage-column column-slug">Acciones</th>
            </tr>
        </tfoot>
    </table>
    <button type="button" id="agregar_etiqueta">Agregar nueva etiqueta</button>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const agregarEtiquetaBtn = document.getElementById('agregar_etiqueta');
        const tablaEtiquetas = document.querySelector('.wp-list-table.tags tbody');

        agregarEtiquetaBtn.addEventListener('click', function() {
            const nuevaFila = document.createElement('tr');
            nuevaFila.innerHTML = `
                <td><input type="text" name="crm_whatsapp_bot_etiquetas[][nombre]"></td>
                <td><input type="color" name="crm_whatsapp_bot_etiquetas[][color]"></td>
                <td><button type="button" class="eliminar_etiqueta">Eliminar</button></td>
            `;
            tablaEtiquetas.appendChild(nuevaFila);

            const eliminarEtiquetaBtn = nuevaFila.querySelector('.eliminar_etiqueta');
            eliminarEtiquetaBtn.addEventListener('click', function() {
                nuevaFila.remove();
            });
        });

        tablaEtiquetas.addEventListener('click', function(event) {
            if (event.target.classList.contains('eliminar_etiqueta')) {
                event.target.closest('tr').remove();
            }
        });
    });
    </script>
    <?php
}
?>
