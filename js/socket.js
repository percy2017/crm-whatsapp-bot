// js/socket.js
jQuery(document).ready(function($) {
    let socket; // Mover la declaración de socket fuera del bloque try

    // Obtener la URL del servidor Socket.IO desde los parámetros de WordPress
    const socketIoUrl = crm_whatsapp_bot_params.socketIoUrl;
    const roomName = crm_whatsapp_bot_params.socketIoRoom;
    // console.log('Socket IO URL: ' + socketIoUrl);
    // console.log('Socket IO Room: ' + roomName);

    try {
        socket = io(socketIoUrl, {
            transports: ['websocket'],
            reconnectionAttempts: 5,
            reconnectionDelay: 3000,
        });

        socket.on('connect', () => {
            // console.log(`[CRM] Successfully connected to Socket.IO server. Socket ID: ${socket.id}`);
            // console.log(`[CRM] Attempting to join room: ${roomName}`);

            socket.emit('join_room', { roomName: roomName }, (response) => {
                if (response && response.success) {
                    console.log(`[CRM] Successfully joined room: ${response.room}. Message: ${response.message}`);
                } else {
                    console.error(`[CRM] Failed to join room: ${roomName}. Server response:`, response);
                }
            });
        });

        socket.on('new_server_data', (data) => {
            console.log('[CRM] Received new_server_data:', data);

            // Dispatch a custom event with the data
            $(document).trigger('crm_new_message', data);
        });

        socket.on('connect_error', (error) => {
            console.error('[CRM] Connection error:', error.message, error);
        });

        socket.on('disconnect', (reason) => {
            console.warn(`[CRM] Disconnected from Socket.IO server. Reason: ${reason}`);
            if (reason === 'io server disconnect') {
                socket.connect();
            }
        });

    } catch (error) {
        console.error('[CRM Socket Client] Error initializing Socket.IO connection:', error);
    }
});
