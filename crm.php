<?php

// Función para crear el menú en el panel de administración
function crm_whatsapp_bot_admin_menu() {
    add_menu_page(
        'CRM 2025',
        'CRM 2025',
        'manage_options',
        'crm-whatsapp-bot',
        'crm_whatsapp_bot_render_ui',
        'dashicons-whatsapp', // Icono del menú
        5 // Posición en el menú
    );
}
add_action( 'admin_menu', 'crm_whatsapp_bot_admin_menu' );

// Función para renderizar la UI del plugin
function crm_whatsapp_bot_render_ui() {
    ?>
    <div class="crm-whatsapp-bot">
        <div class="crm-whatsapp-bot__sidebar crm-whatsapp-bot__sidebar--left">
            <div class="crm-whatsapp-bot__sidebar-header">
                <div style="display: flex;">
                    <input type="text" placeholder="Buscar" class="crm-whatsapp-bot__sidebar-search" style="flex-grow: 1;">
                    <button class="crm-whatsapp-bot__new-chat-button">+</button>
                </div>
            </div>
            <div class="crm-whatsapp-bot__new-chat" style="display: none;">
                <input type="text" placeholder="Número de teléfono" class="crm-whatsapp-bot__new-chat-input">
                <button class="crm-whatsapp-bot__new-chat-start">Iniciar conversación</button>
            </div>
            <div class="crm-whatsapp-bot__sidebar-chats">
                <ul class="crm-whatsapp-bot__chat-list"></ul>
            </div>
        </div>

        <div class="crm-whatsapp-bot__chat-content">
            <div class="crm-whatsapp-bot__chat">
                <div class="crm-whatsapp-bot__chat-header">
                    <div class="crm-whatsapp-bot__chat-avatar">
                        <img src="data:image/svg+xml,%3Csvg%20width%3D%2240%22%20height%3D%2240%22%20viewBox%3D%220%200%20100%20100%22%20fill%3D%22none%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Ccircle%20cx%3D%2250%22%20cy%3D%2250%22%20r%3D%2250%22%20fill%3D%22%23E0E0E0%22%2F%3E%3Ccircle%20cx%3D%2250%22%20cy%3D%2235%22%20r%3D%2215%22%20fill%3D%22%23BDBDBD%22%2F%3E%3Cpath%20d%3D%22M50%2065C30%2065%2015%2080%2015%20100H85C85%2080%2070%2065%2050%2065Z%22%20fill%3D%22%23BDBDBD%22%2F%3E%3C%2Fsvg%3E" alt="Avatar">
                    </div>
                    <div class="crm-whatsapp-bot__chat-info">
                        <div class="crm-whatsapp-bot__chat-name"></div>
                        <div class="crm-whatsapp-bot__chat-last-message"></div>
                    </div>
                </div>
                <div class="crm-whatsapp-bot__chat-body"></div>
                <div class="crm-whatsapp-bot__chat-footer">
                    <div class="send_tools">
                        <button class="send_tools_attach" type="button">...</button>
                        <button class="send_tools_emojis" type="button">...</button>
                        <button class="send_tools_quick" type="button">...</button>
                    </div>
                    <textarea placeholder="Escribe un mensaje" class="crm-whatsapp-bot__chat-input"></textarea>
                    <button class="crm-whatsapp-bot__chat-send">Enviar</button>
                </div>
            </div>
         </div>

        <div class="crm-whatsapp-bot__sidebar crm-whatsapp-bot__sidebar--right crm-whatsapp-bot__chat-data">
            <div class="crm-whatsapp-bot__chat-header">
                <h2>Info de Contacto</h2>
            </div>
            Datos del chat y contacto
        </div>
    </div>
    <?php
}
?>
