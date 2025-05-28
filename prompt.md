# Plugins para wordpress (admin)

## CRM WhatsApp Bot - Desarrollo de la Interfaz de Usuario

## Descripción General del Proyecto

Este proyecto tiene como objetivo crear un plugin de CRM para WordPress que se integre con WhatsApp utilizando Evolution API y Socket.IO. El enfoque inicial es el desarrollo de una interfaz de usuario (UI) que imite la apariencia de WhatsApp Web, **dentro del dashboard de WordPress**, con la diferencia clave de la capacidad de gestionar múltiples instancias de WhatsApp.

## Funcionalidades Clave

*   **Clon de la UI:** La UI debe ser una réplica cercana de WhatsApp Web, proporcionando una experiencia de usuario familiar e intuitiva. **La UI debe estar dentro del dashboard de WordPress.**
*   **Lista de Chats:** La UI debe mostrar una lista de chats activos.
    *   Se ha implementado la visualización de la lista de chats en la barra lateral, obteniendo los datos de la API de Evolution y actualizando la UI en tiempo real a través de Socket.IO.
*   **Identificación de Instancias:** Cada chat en la lista debe indicar claramente a qué instancia de WhatsApp pertenece.
    *   Se ha implementado la visualización de la instancia de WhatsApp a la que pertenece cada chat.
*   **Manejo de Mensajes:** La UI debe permitir a los usuarios enviar y recibir mensajes (inicialmente simulados) a través de las instancias correspondientes.
    *   Se ha implementado la visualización de los mensajes en el área de chat principal, mostrando los mensajes entrantes y salientes.
*   **Consistencia Visual:** La visualización de los mensajes enviados y recibidos debe coincidir precisamente con el estilo de WhatsApp Web.
*   **Obtener datos del contacto:** Se llama a la API de Evolution para obtener el nombre y el avatar del contacto.
*   **Mostrar datos del contacto:** Se muestra el nombre y el avatar del contacto en la lista de chats y en el encabezado del chat.
*   **Mostrar mensajes en tiempo real:** Los mensajes se muestran en tiempo real en la UI a través de Socket.IO.
*   **Seleccionar chat:** Al hacer clic en un chat en la barra lateral, se muestra el historial de mensajes en el área de chat principal.

## Tecnologías

*   WordPress
*   PHP
*   JavaScript
*   CSS
*   jQuery
*   Evolution API
*   Socket.IO

## Estructura de Directorios

```
crm-whatsapp-bot/
├── crm-whatsapp-bot.php      (Archivo principal del plugin)
├── crm.php                   (Estructura HTML de la UI)
├── api.php                   (webhook)
├── settings.php              (getopcion() para evolution api, socketIO y 
OpenAI)
├── api.php                   (webhook para recivir los chats)
├── css/
│   │   └── crm.css
│   └── js/
│       └── crm.js
```

## Próximos Pasos

*   Manejar los mensajes multimedia (imágenes, videos, audio, documentos).
