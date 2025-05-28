# CRM WhatsApp Bot

## Descripción

Plugin de CRM para WordPress que se integra con WhatsApp utilizando Evolution API, Socket.IO y Modelos de IA. Permite la gestión de conversaciones, el envío de mensajes y la integración con modelos de IA para automatización.

## Versión

1.0.1

## Autor

Percy Alvarez

## Instalación

1.  Subir el plugin a través del panel de administración de WordPress.
2.  Activar el plugin.
3.  Configurar los ajustes en la página de configuración del plugin (Ajustes > Crm WhatsApp Bot).

## Configuración

*   **Evolution API:**
    *   URL de la API
    *   Clave de la API
*   **Socket.IO:**
    *   URL del servidor Socket.IO
    *   Sala de Socket.IO (por defecto: crm-whatsapp-bot)
*   **OpenAI:**
    *   URL de la API
    *   Clave de la API

## Dependencias

*   WordPress
*   Evolution API
*   Socket.IO
*   SweetAlert2
*   intl-tel-input

## Uso

El plugin añade una página de administración en el menú de WordPress llamada "CRM 2025". Desde esta página, puedes gestionar las conversaciones de WhatsApp, enviar mensajes y ver la información de los contactos. El plugin utiliza Socket.IO para la comunicación en tiempo real con el servidor. El plugin utiliza la API de Evolution para enviar y recibir mensajes de WhatsApp.

## Funcionalidades

*   Integración con la API de Evolution para enviar y recibir mensajes de WhatsApp.
*   Comunicación en tiempo real mediante Socket.IO.
*   Gestión de contactos y conversaciones.
*   Integración con modelos de IA (requiere configuración de la API de OpenAI).
*   Subida de avatares de usuario.
*   Envío de mensajes de texto, imágenes, videos y audios.

## Créditos

Menciones a librerías y APIs utilizadas (Evolution API, Socket.IO, SweetAlert2, intl-tel-input).

## Licencia

Especificar la licencia del plugin.
