<?php

require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

// Función para registrar el endpoint del webhook
add_action( 'rest_api_init', function () {
    register_rest_route( 'crm-whatsapp-bot/v1', '/webhook', array(
        'methods'  => 'POST',
        'callback' => 'crm_whatsapp_bot_webhook_handler',
    ) );
} );
// Función para manejar las peticiones al webhook
function crm_whatsapp_bot_webhook_handler( WP_REST_Request $request ) {
    // Obtener los datos del cuerpo de la petición
    $data = $request->get_json_params();

    // Registrar los datos recibidos
    // error_log( 'Webhook recibido - Datos JSON: ' . json_encode( $data ) );

    // mostrando datos relevante
    error_log('message_id_wa: ' . sanitize_text_field($data['data']['key']['id'] ?? ''));
    error_log('timestamp_wa: ' . $data['data']['messageTimestamp']);
    error_log('Conversation: ' . json_encode(isset($data['data']['message']['conversation']) ? $data['data']['message']['conversation'] : null));
    error_log('extendedTextMessage: ' . json_encode(isset($data['data']['message']['extendedTextMessage']['text']) ? $data['data']['message']['extendedTextMessage']['text'] : null));
    error_log('imageMessage: ' . json_encode(isset($data['data']['message']['imageMessage']['caption']) ? $data['data']['message']['imageMessage']['caption'] : null));
    error_log('videoMessage: ' . json_encode(isset($data['data']['message']['videoMessage']['caption']) ? $data['data']['message']['videoMessage']['caption'] : null));
    
    // Extraer Contenido del Mensaje
    $message_id_wa = sanitize_text_field($data['data']['key']['id'] ?? '');
    $timestamp_wa = (int) $data['data']['messageTimestamp'];
    $from_me = (bool) ($data['data']['key']['fromMe'] ?? false);
    $tipo_mensaje = isset($data['data']['key']['fromMe']) && $data['data']['key']['fromMe'] ? 'output' : 'input';
    $message_id_wa = sanitize_text_field($data['data']['key']['id'] ?? '');
    $phone_number = $data['data']['key']['remoteJid'];
    $instance_name = $data['instance'];
    $message_text = '';
    $media_caption = null;
    $media_mimetype = null;
    $media_base64 = null;
    $tipo_contenido = 'text';

    $message_info = $data['data']['message'];
    if (isset($message_info['conversation'])) {
         $message_text = $message_info['conversation'];
    } elseif (isset($message_info['extendedTextMessage']['text'])) {
         $message_text = $message_info['extendedTextMessage']['text'];
    } else {
        if (isset($message_info['imageMessage'])) {
            $message_text = $message_info['imageMessage']['caption'] ?? null;
            $media_mimetype = $message_info['imageMessage']['mimetype'] ?? 'image/jpeg'; // Default
            $media_base64 = $data['data']['message']['base64'] ?? null;
            if ($media_base64){
                $tipo_contenido = 'image';
            }
        } elseif (isset($message_info['videoMessage'])) {
            $message_text = $message_info['videoMessage']['caption'] ?? null;
            $media_mimetype = $message_info['videoMessage']['mimetype'] ?? 'video/mp4'; // Default
            $media_base64 = $data['data']['message']['base64'] ?? null;
            if ($media_base64){
                $tipo_contenido = 'video';
            }
        } elseif (isset($message_info['audioMessage'])) {
            $media_caption = null; 
            $raw_mimetype = $message_info['audioMessage']['mimetype'] ?? 'audio/ogg';
            $mime_parts = explode(';', $raw_mimetype);
            $media_mimetype = trim($mime_parts[0]);
            $media_base64 = $data['data']['message']['base64'] ?? null;
            if ($media_base64){
                $tipo_contenido = 'audio';
            }
        } elseif (isset($message_info['documentWithCaptionMessage'])) {
            $doc_msg = $message_info['documentWithCaptionMessage']['message']['documentMessage'] ?? null;
            if ($doc_msg) {
                $media_caption = $doc_msg['caption'] ?? null;
                $media_mimetype = $doc_msg['mimetype'] ?? 'application/octet-stream';
                $filename = $doc_msg['fileName'] ?? null;
                $message_text = $media_caption ?: $filename;
            }
        } elseif (isset($message_info['documentMessage'])) {
            $media_caption = $message_info['documentMessage']['caption'] ?? null;
            $media_mimetype = $message_info['documentMessage']['mimetype'] ?? 'application/octet-stream'; // Default genérico
            $message_text = $media_caption ?: ($message_info['documentMessage']['fileName'] ?? null);
        }
    }

    // Si hay base64 pero no se detectó tipo media, intentar deducir (o marcar como 'file')
    if ($media_base64 && $tipo_contenido === 'text') {
         $tipo_contenido = 'file';
    }
 
    $user_id = crm_whatsapp_bot_get_profile_data( $instance_name, $phone_number, $message_text);
    if ($media_base64) {
        $media_id = crm_process_base64_media($media_base64, $media_mimetype, $message_text) ?? '';      
    }

    // Preparar los datos para enviar al Socket.IO y DB
    $data_socket_db = array(
        'content_type' => $tipo_contenido, // text, image, vidoe,  etc
        'message' => $message_text, // mensaje o caption de chat
        'instance' => $data['instance'], // instancia de evolutiona api
        'sender' => $data['data']['key']['remoteJid'], // xxx@s.whatsapp.net
        'message_type' => $tipo_mensaje, // entrada/salida
        'timestamp' => $data['data']['messageTimestamp'], //fecha y hora formato whatsapp
        'user_id' => $user_id, // id del usuario
        'media_id' => $media_id, //id del archivo adjunto
        'message_id_wa' => $message_id_wa // id del chat 
    );

    // Enviar los datos al Socket.IO server
    crm_whatsapp_bot_send_to_crm( $data_socket_db );

    // Guardar los datos del mensaje
    crm_whatsapp_bot_save_message( $data_socket_db );

    $post_id = array( 'message' => 'Webhook recibido correctamente' );
}

// Función para obtener datos para la UI
add_action( 'rest_api_init', function () {
    register_rest_route( 'crm-whatsapp-bot/v1', '/get_data', array(
        'methods'  => 'GET',
        'callback' => 'crm_whatsapp_bot_get_data',
    ) );
} );
// funcion para obtener datos para la UI
function crm_whatsapp_bot_get_data( WP_REST_Request $request ) {
    $type = $request->get_param( 'type' );

    if ( $type === 'get_chats' ) {
        $args = array(
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'crm_instance',
                    'value' => '',
                    'compare' => '!=',
                ),
                array(
                    'key' => 'crm_last_message_timestamp',
                    'compare' => 'EXISTS',
                ),
            ),
            'orderby' => 'meta_value_num',
            'meta_key' => 'crm_last_message_timestamp',
            'order' => 'DESC',
        );

        $users = get_users( $args );

        $chat_data = array();

        foreach ( $users as $user ) {
            // $remote_jid = str_replace( '_' . get_user_meta( $user->ID, 'crm_instance', true ), '@s.whatsapp.net', $user->user_login );
            $avatar_id = get_user_meta( $user->ID, 'wp_user_avatar', true );
            $avatar_url = wp_get_attachment_image_src( $avatar_id, 'thumbnail' );

            $chat_data[] = array(
                'id' => $user->ID,
                'email' => $user->user_email,
                'login' => $user->user_login,
                'name' => $user->display_name ?? '',
                'billing_phone' => get_user_meta( $user->ID, 'billing_phone', true ) ?? '',
                'picture' => $avatar_url[0] ?? '',
                'instance' => get_user_meta( $user->ID, 'crm_instance', true ) ?? '',
                'registered' => $user->user_registered,
                'crm_last_message' => get_user_meta( $user->ID, 'crm_last_message', true ) ?? '',
                'crm_last_message_timestamp' => get_user_meta( $user->ID, 'crm_last_message_timestamp', true ) ?? '',
            );
        }

        return $chat_data;
    } elseif ( $type === 'get_history' ) {
        $user_id = $request->get_param( 'user_id' );

        $args = array(
            'post_type' => 'crm-whatsapp-bot',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'user_id',
                    'value' => $user_id,
                    'compare' => '=',
                )
            ),
            'orderby' => 'modified',
            'order' => 'ASC'
        );

        $chat_history = new WP_Query( $args );

        $history_data = array();

        if ( $chat_history->have_posts() ) {
            while ( $chat_history->have_posts() ) {
                $chat_history->the_post();
                $media_id = get_post_meta( get_the_ID(), 'media_id', true );
                $media_url = $media_id ? wp_get_attachment_url( $media_id ) : '';
                $history_data[] = array(
                    'message' => get_post_meta( get_the_ID(), 'message', true ),
                    'timestamp' => get_post_meta( get_the_ID(), 'timestamp', true ), //fecha y hora de whatsapp
                    'message_type' => get_post_meta( get_the_ID(), 'message_type', true ),
                    'content_type' => get_post_meta( get_the_ID(), 'content_type', true ),
                    'media_url' => $media_url,
                    'post_date' => get_the_date( 'Y-m-d H:i:s', get_the_ID() ) //fecha y hora de wordpress

                );
            }
            wp_reset_postdata();
        }

        return $history_data;
    } else {
        return array( 'message' => 'Invalid data type' );
    }
}

// Función para enviar mensajes desde la UI
add_action( 'rest_api_init', function () {
    register_rest_route( 'crm-whatsapp-bot/v1', '/send_message', array(
        'methods'  => 'POST',
        'callback' => 'crm_whatsapp_bot_send_message_handler',
    ) );
} );

function crm_whatsapp_bot_send_message_handler( WP_REST_Request $request ) {
    // Obtener los datos del cuerpo de la petición
    $data = $request->get_json_params();

    // Registrar los datos recibidos
    error_log( 'Mensaje recibido para enviar - Datos JSON: ' . json_encode( $data ) );

    // Extraer los datos del mensaje
    $message = sanitize_text_field( $data['message'] ?? '' );
    $user_id = intval( $data['user_id'] ?? 0 );

    // Obtener la URL del servidor Evolution API y la clave de API desde las opciones de WordPress
    $server_url = get_option( 'evolution_api_url' );
    $api_key = get_option( 'evolution_api_key' );

    // Obtener el número de teléfono del usuario
    $phone_number = get_user_meta( $user_id, 'billing_phone', true );
    $instance_name = get_user_meta( $user_id, 'crm_instance', true );

    // Validar que los datos necesarios estén presentes
    if ( empty( $message ) || empty( $user_id ) || empty( $phone_number ) || empty( $instance_name ) ) {
        return new WP_Error( 'missing_data', 'Faltan datos para enviar el mensaje.', array( 'status' => 400 ) );
    }

    // Preparar los datos para enviar a la API de Evolution
    $body = array(
        'number'  => $phone_number,
        'options' => array(
            'delay' => 1200,
            'linkPreview' => true,
            'presence' => 'composing',
        ),
        'textMessage' => array (
            'text' => $message
        )
    );

    $args = array(
        'method'  => 'POST',
        'headers' => array(
            'Content-Type' => 'application/json',
            'apikey' => $api_key,
        ),
        'body'    => wp_json_encode( $body ),
        'timeout' => 30,
    );

    // Enviar la petición a la API de Evolution
    $url = trailingslashit( $server_url ) . "message/sendText/" . $instance_name;
    $response = wp_remote_request( $url, $args );

    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        error_log( 'Error al enviar el mensaje a través de la API de Evolution: ' . $error_message );
        return new WP_Error( 'evolution_api_error', 'Error al enviar el mensaje a través de la API de Evolution: ' . $error_message, array( 'status' => 500 ) );
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );

    error_log( 'Respuesta de la API de Evolution: ' . $response_body );
    error_log( 'Respuesta response_code: ' . $response_code );

    // Si la petición fue exitosa, guardar el mensaje en la base de datos
    if ( $response_code === 201 ) {
        // Preparar los datos para guardar en la base de datos
        $message_data = array(
            'content_type' => 'text',
            'message' => $message,
            'instance' => $instance_name,
            'sender' => $phone_number . '@s.whatsapp.net',
            'message_type' => 'output',
            'timestamp' => time(),
            'user_id' => $user_id,
            'media_id' => '',
            'message_id_wa' => '' // TODO: Obtener el ID del mensaje de la API de Evolution
        );

        // Guardar el mensaje en la base de datos
        crm_whatsapp_bot_save_message( $message_data );

        //actualizar users
        update_user_meta( $user_id, 'crm_last_message', $message );
        update_user_meta( $user_id, 'crm_last_message_timestamp', time() );

        // Crear una respuesta de API REST válida
        $response = new WP_REST_Response( array( 'message' => 'Mensaje enviado correctamente.' ), 200 );
        $response->set_headers( array( 'Content-Type' => 'application/json' ) );

        return $response;
    } else {
        return new WP_Error( 'evolution_api_error', 'Error al enviar el mensaje a través de la API de Evolution: ' . $response_body, array( 'status' => 500 ) );
    }
}


// ------------------------- funciones extras -------------------------
// --------------------------------------------------------------------

/**
 * Función para obtener los datos del perfil de un contacto desde la API de Evolution.
 */
function crm_whatsapp_bot_get_profile_data( $instance_name, $phone_number, $last_message ) {

    // Obtener la URL del servidor Evolution API y la clave de API desde las opciones de WordPress
    $server_url = get_option( 'evolution_api_url' );
    $api_key = get_option( 'evolution_api_key' );

    // Buscar si existe un usuario
    $phone = str_replace('@s.whatsapp.net', '', $phone_number);
    $user = get_user_by( 'login', $phone."_".$instance_name );
    if ( ! $user ) {
        // Crear un nuevo usuario si no existe
        $args = array(
            'method'  => 'POST',
            'headers' => array(
                'Content-Type' => 'application/json',
                'apikey' => $api_key,
            ),
            'body'    => wp_json_encode(  ['number' => $phone ] ),
            'timeout' => 30,
        );
        $response = wp_remote_request( trailingslashit( $server_url ) . "chat/fetchProfile/" . $instance_name, $args );
        $response_body = wp_remote_retrieve_body( $response );
        $decoded_body = json_decode( $response_body, true );
        if (!is_array($decoded_body)) {
            $decoded_body = array();
        }
        $user_data = array(
            'user_login' => $phone."_".$instance_name,
            'user_pass'  => wp_generate_password(), // Generar una contraseña aleatoria
            'user_email' => $phone . '@localhost.dev', // Generar un correo electrónico
            'first_name' => $decoded_body['name'] ?? '', // Usar el nombre del perfil
        );

        $user_id = wp_insert_user( $user_data );
        if ( is_wp_error( $user_id ) ) {
            error_log( 'Error al crear el usuario: ' . $user_id->get_error_message() );
        } else {
            error_log( 'Usuario creado con ID: ' . $user_id );

            //agregar metas al usuarios nuevo
            update_user_meta( $user_id, 'billing_phone', $phone );
            update_user_meta( $user_id, 'crm_instance', $instance_name );
            update_user_meta( $user_id, 'crm_last_message', $last_message );
            update_user_meta( $user_id, 'crm_last_message_timestamp', time() );
            // Guardar el avatar del usuario
            $avatar_url = $decoded_body['picture'];
            if ( ! empty( $avatar_url ) ) {
                $avatar_id = crm_whatsapp_bot_upload_avatar( $avatar_url, $user_id, $phone );
                if ( $avatar_id ) {
                    // Actualizar la imagen de perfil del usuario
                    update_user_meta( $user_id, 'wp_user_avatar', $avatar_id );
                    error_log( 'Avatar del usuario actualizado con ID: ' . $avatar_id );
                } else {
                    error_log( 'Error al subir el avatar del usuario.' );
                }
            }
        }
        return $user_id;
    }else{
        update_user_meta( $user->ID, 'crm_last_message', $last_message );
        update_user_meta( $user->ID, 'crm_last_message_timestamp', time() );
        return $user->ID;
    }    
}

/**
 * Sube el avatar del usuario a la biblioteca de medios y lo asocia al usuario.
 */
function crm_whatsapp_bot_upload_avatar( $image_url, $user_id, $name) {
    // Descargar la imagen
    $tmp = download_url( $image_url );
    if ( is_wp_error( $tmp ) ) {
        error_log( 'Error al descargar la imagen: ' . $tmp->get_error_message() );
        return false;
    }

    // Configurar los datos del archivo
    $file_array = array(
        'name'     => $name.'.jpg',
        'tmp_name' => $tmp,
    );

    // Subir el archivo
    $attachment_id = media_handle_sideload( $file_array, 0 );

    // Si hay un error al subir el archivo
    if ( is_wp_error( $attachment_id ) ) {
        error_log( 'Error al subir el archivo: ' . $attachment_id->get_error_message() );
        @unlink( $tmp );
        return false;
    }

    // Actualizar los metadatos del archivo adjunto
    update_post_meta( $attachment_id, '_wp_attached_user', $user_id );

    // Limpiar el archivo temporal
    @unlink( $tmp );

    return $attachment_id;
}

/**
 * Procesa datos Base64, los guarda en la Media Library y devuelve la URL del adjunto.
 */
function crm_process_base64_media( $base64_data, $mime_type, $caption = null ) {

    // Decodificar Base64
    $decoded_data = base64_decode( $base64_data );
    if ( $decoded_data === false ) {
        error_log( "[crm_process_base64_media] Error: No se pudo decodificar los datos Base64." );
        // return new WP_Error( 'base64_decode_failed', 'No se pudo decodificar los datos Base64.' );
        return null;
    }

    // Generar un nombre de archivo único
    $upload_dir = wp_upload_dir();
    $extension = mime_content_type_to_extension( $mime_type ); // Necesita una función auxiliar o mapeo
    if(!$extension) $extension = 'bin'; // Extensión por defecto si no se mapea
    $filename = wp_unique_filename( $upload_dir['path'], 'wa_media_' . time() . '.' . $extension );

    // Guardar los datos decodificados en un archivo temporal (o directamente con wp_upload_bits)
    $upload = wp_upload_bits( $filename, null, $decoded_data );

    if ( ! empty( $upload['error'] ) ) {
        error_log( "[crm_process_base64_media] Error en wp_upload_bits: " . $upload['error'] );
        // return new WP_Error( 'wp_upload_bits_failed', $upload['error'] );
        return null;
    }

    // Preparar datos para wp_insert_attachment
    $attachment = array(
        'guid'           => $upload['url'],
        'post_mime_type' => $mime_type,
        'post_title'     => sanitize_file_name( $caption ?: $filename ), // Usar caption o filename como título
        'post_content'   => '', // Descripción si la hubiera
        'post_status'    => 'inherit',
    );

    // Insertar el adjunto en la base de datos
    $attachment_id = wp_insert_attachment( $attachment, $upload['file'] );

    if ( is_wp_error( $attachment_id ) ) {
        error_log( "[crm_process_base64_media] Error en wp_insert_attachment: " . $attachment_id->get_error_message() );
        @unlink( $upload['file'] ); // Eliminar archivo si falla la inserción
        return null;
    }

    // Generar metadatos del adjunto (ej: miniaturas para imágenes)
    $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
    wp_update_attachment_metadata( $attachment_id, $attachment_data );

    // Obtener la URL del archivo adjunto
    // $upload_url = wp_get_attachment_url( $attachment_id );

    // error_log( "[crm_process_base64_media] Adjunto creado con éxito. URL: {$upload_url}" );
    return $attachment_id;
}

/**
 * Convierte un MIME type a una extensión de archivo común.
 */
function mime_content_type_to_extension($mime_type) {
    $mime_map = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        'video/mp4'  => 'mp4',
        'video/mpeg' => 'mpeg',
        'video/quicktime' => 'mov',
        'audio/mpeg' => 'mp3',
        'audio/ogg'  => 'ogg',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        // Añadir más mapeos según sea necesario
    ];
    return $mime_map[$mime_type] ?? false;
}

// Función para enviar los datos a crm.php utilizando Socket.IO
function crm_whatsapp_bot_send_to_crm( $data ) {
    // Obtener la URL del servidor Socket.IO
    $socket_io_url = get_option( 'socket_io_url' );
    $socket_io_room = get_option( 'socket_io_room', 'crm-whatsapp-bot' );

    error_log( 'Socket.IO URL: ' . $socket_io_url . ' - Data: ' . json_encode($data) );

    // Verificar si la URL del servidor Socket.IO está configurada
    if ( empty( $socket_io_url ) ) {
        error_log( 'Error: La URL del servidor Socket.IO no está configurada.' );
        return;
    }

    error_log('Data dump: ' . json_encode($data));

    $body = array(
        'roomName' => $socket_io_room,
        'dataToEmit' => $data,
    );

    error_log('Body antes de json_encode: ' . json_encode($body));

    $args = array(
        'method'  => 'POST',
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body'    => wp_json_encode( $body ),
    );

    // Enviar la petición POST a /api/emit
    $url = trailingslashit( $socket_io_url ) . 'api/emit';
    error_log( 'Enviando petición a: ' . $url );
    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        error_log( 'Error al enviar datos a crm.php: ' . $error_message );
        return;
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    $response_body = wp_remote_retrieve_body( $response );

    error_log( 'Respuesta del servidor Socket.IO: ' . $response_body );
}

/**
 * Guarda los datos del mensaje como un post de tipo crm-whatsapp-bot.
 */
function crm_whatsapp_bot_save_message( $message_data ) {
    $post_data = array(
        'post_title'    => 'Mensaje de WhatsApp',
        'post_status'   => 'publish',
        'post_type'     => 'crm-whatsapp-bot',
    );

    $post_id = wp_insert_post( $post_data );

    if ( ! is_wp_error( $post_id ) ) {
        // Guardar los datos del mensaje como metadatos del post
        foreach ( $message_data as $key => $value ) {
            update_post_meta( $post_id, $key, $value );
        }
        error_log( 'Mensaje guardado con ID: ' . $post_id );
    } else {
        error_log( 'Error al guardar el mensaje: ' . $post_id->get_error_message() );
    }
}

?>
