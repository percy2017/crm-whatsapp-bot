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
?>