jQuery(document).ready(function($) {
    let mediaId = null; // Declarar la variable global mediaId aquí
    // Function to load chats
    function loadChats() {
        $.ajax({
            url: '/wp-json/crm-whatsapp-bot/v1/get_data?type=get_chats',
            method: 'GET',
            success: function(data) {
                // console.log(data)
                let chatListHtml = '';
                data.forEach(chat => {
                    chatListHtml += `
                        <li data-user-id="${chat.id}" style="cursor: pointer;">
                            <div style="display: flex;">
                                <div class="crm-whatsapp-bot__chat-avatar">
                                    <img src="${chat.picture}" alt="Avatar">
                                </div>
                                <div class="crm-whatsapp-bot__chat-info">
                                    <div class="crm-whatsapp-bot__chat-name">${chat.name}</div>
                                    <div class="crm-whatsapp-bot__chat-last-message">${chat.crm_last_message}</div>
                                    <div class="crm-whatsapp-bot__chat-instance">${chat.instance}</div>
                                </div>
                            </div>
                        </li>
                    `;
                });
                $('.crm-whatsapp-bot__sidebar-chats ul').html(chatListHtml);

                $('.crm-whatsapp-bot__chat-list li').on('click', function() {
                    $('.crm-whatsapp-bot__chat-list li').removeClass('active');
                    $(this).addClass('active');
                    $('.crm-whatsapp-bot__chat-header').show();
                    loadChatHistory($(this).data('user-id'));
                });
            },
            error: function(error) {
                console.error('[CRM] Error loading chats:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cargar los chats',
                    text: error.responseJSON.message,
                });
            }
        });
    }

    // Function to load chat history
    function loadChatHistory(userId) {
        $.ajax({
            url: `/wp-json/crm-whatsapp-bot/v1/get_data?type=get_history&user_id=${userId}`,
            method: 'GET',
            success: function(data) {
                // console.log(data)
                let chatBodyHtml = '';
                data.forEach(message => {
                    let messageHtml = '';
                    const messageClass = (message.message_type === 'input') ? 'crm-whatsapp-bot__message--received' : 'crm-whatsapp-bot__message--sent';
                    if (message.content_type === 'image') {
                        messageHtml = `
                            <div class="crm-whatsapp-bot__message-container ${messageClass}">
                                <img src="${message.media_url}" alt="Image" class="crm-whatsapp-bot__message-media">
                                <div class="crm-whatsapp-bot__message-caption">${message.message}</div>
                            </div>
                        `;
                    } else if (message.content_type === 'video') {
                        messageHtml = `<div class="crm-whatsapp-bot__message ${messageClass}"><video src="${message.media_url}" controls class="crm-whatsapp-bot__message-media"></video></div>`;
                    } else if (message.content_type === 'audio') {
                        messageHtml = `<div class="crm-whatsapp-bot__message ${messageClass}"><audio controls> <source src="${message.media_url}"></audio></div>`;
                    } else {
                        messageHtml = `<div class="crm-whatsapp-bot__message ${messageClass}">${message.message}</div>`;
                    }
                    chatBodyHtml += messageHtml;
                });
                $('.crm-whatsapp-bot__chat-body').html(chatBodyHtml);

                // Scroll to bottom of chat
                $('.crm-whatsapp-bot__chat-body').scrollTop($('.crm-whatsapp-bot__chat-body')[0].scrollHeight);
            },
            error: function(error) {
                console.error('[CRM] Error loading chat history:', error);
            }
        });
    }

    // New chat button click handler
    $('.crm-whatsapp-bot__sidebar-header').on('click', '.crm-whatsapp-bot__new-chat-button', function() {
        console.log('crm-whatsapp-bot__new-chat-button')
        $('.crm-whatsapp-bot__new-chat').toggle();
        $('.crm-whatsapp-bot__sidebar-chats').toggle();
    });

     // Media library button click handler
    $(document).on('click', '.send_tools_attach', function(e) {
        e.preventDefault();

        // Open media library
        if (wp && wp.media) {
            const mediaUploader = wp.media({
                title: 'Seleccionar archivo multimedia',
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                mediaId = attachment.id;
                mediaId = mediaId;
            });

            mediaUploader.open();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'La Media Library no está disponible.',
            });
        }
    });

   // Emoji button click handler
    $(document).on('click', '.send_tools_emojis', function() {
        tb_show('Emojis', '#TB_inline?inlineId=crm-whatsapp-bot-emoji-modal', true);
    });

    $(document).on('click', '.crm-whatsapp-bot-emoji-button', function() {
        var emoji = $(this).data('emoji');
        $('.crm-whatsapp-bot__chat-input').val($('.crm-whatsapp-bot__chat-input').val() + emoji);
        tb_remove();
    });

    // Function to filter emojis
    $('#emoji-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.crm-whatsapp-bot-emoji-button').each(function() {
            var emojiName = $(this).data('name').toLowerCase();
            if (emojiName.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Initialize intl-tel-input
    const input = document.querySelector(".crm-whatsapp-bot__new-chat-input");
    if (input) {
        window.intlTelInput(input, {
            loadUtils: () => import("https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.1/build/js/utils.js"),
        });
    }

    // New chat start button click handler
    $('.crm-whatsapp-bot__new-chat').on('click', '.crm-whatsapp-bot__new-chat-start', function() {
        const phoneNumber = iti.getNumber();
        console.log('[CRM] Starting new chat with phone number:', phoneNumber);
        // TODO: Implement logic to start a new chat with the phone number
        Swal.fire({
            title: 'Starting new chat',
            text: 'Starting new chat with phone number: ' + phoneNumber,
            icon: 'info',
        });
        $('.crm-whatsapp-bot__new-chat').toggle();
        $('.crm-whatsapp-bot__sidebar-chats').toggle();
    });

    // Listen for the custom event 'crm_new_message'
    $(document).on('crm_new_message', function(event, data) {
        console.log('[CRM] Received crm_new_message event:', data);
        loadChats()
    });

    // Handle click event on chat list items
    $('.crm-whatsapp-bot__sidebar-chats ul').on('click', 'li', function() {
        const userId = $(this).data('user-id');
        const userName = $(this).find('.crm-whatsapp-bot__chat-name').text();
        const userPicture = $(this).find('.crm-whatsapp-bot__chat-avatar img').attr('src');

        console.log('[CRM] Clicked on chat with userId:', userId);

        // Update chat header
        $('.crm-whatsapp-bot__chat-header .crm-whatsapp-bot__chat-name').text(userName);
        $('.crm-whatsapp-bot__chat-header .crm-whatsapp-bot__chat-avatar img').attr('src', userPicture);

        // Clear existing messages
        $('.crm-whatsapp-bot__chat-body').empty();

        // Load chat history
        loadChatHistory(userId);
        $('.crm-whatsapp-bot__chat-data').hide()
        mediaId = null
        $('.crm-whatsapp-bot__chat-input').val('')
    });

    // Load chats on document ready
    loadChats();

    // Event listener for chat header click
    $('.crm-whatsapp-bot__chat-header').on('click', function() {
        $('.crm-whatsapp-bot__chat-data').show();

        let userId = $('.crm-whatsapp-bot__chat-list li.active').data('user-id');

        $.ajax({
            url: `/wp-json/crm-whatsapp-bot/v1/get_data?type=get_user&user_id=${userId}`,
            method: 'GET',
            success: function(data) {
                // Insertar la información del contacto en el sidebar derecho
                $('.crm-whatsapp-bot__chat-data .crm-whatsapp-bot__chat-data-name').val(data.name);
                $('.crm-whatsapp-bot__chat-data .crm-whatsapp-bot__chat-data-email').val(data.email);
                $('.crm-whatsapp-bot__chat-data .crm-whatsapp-bot__chat-data-phone').val(data.billing_phone);

                // Cargar los roles al select
                let rolesHtml = '';
                data.roles.forEach(role => {
                    rolesHtml += `<option value="${role.id}" ${data.role === role.id ? 'selected' : ''}>${role.name}</option>`;
                });
                $('.crm-whatsapp-bot__chat-data .crm-whatsapp-bot__chat-data-role').html(rolesHtml);

                // Cargar las etiquetas al select
                let etiquetasHtml = '';
                data.etiquetas.forEach(etiqueta => {
                    etiquetasHtml += `<option value="${etiqueta.nombre}" ${data.crm_etiqueta === etiqueta.nombre ? 'selected' : ''}>${etiqueta.nombre}</option>`;
                });
                $('.crm-whatsapp-bot__chat-data .crm-whatsapp-bot__chat-data-etiqueta').html(etiquetasHtml);

                // Cargar las instancias al select
                let instancesHtml = '';
                data.instances.forEach(instance => {
                    instancesHtml += `<option value="${instance}" ${data.instance === instance ? 'selected' : ''}>${instance}</option>`;
                });
                $('.crm-whatsapp-bot__chat-data .crm-whatsapp-bot__chat-data-instance').html(instancesHtml);
            },
            error: function(error) {
                console.error('[CRM] Error loading user data:', error);
            }
        });
    });

    $('.crm-whatsapp-bot__chat-data-save').on('click', function() {
            // Event listener for save button click
        let userId = $('.crm-whatsapp-bot__chat-list li.active').data('user-id');
        let name = $('.crm-whatsapp-bot__chat-data .crm-whatsapp-bot__chat-data-name').val();
        let email = $('.crm-whatsapp-bot__chat-data .crm-whatsapp-bot__chat-data-email').val();
        let billing_phone = $('.crm-whatsapp-bot__chat-data .crm-whatsapp-bot__chat-data-phone').val();
        let role = $('.crm-whatsapp-bot__chat-data .crm-whatsapp-bot__chat-data-role').val();
        let crm_etiqueta = $('.crm-whatsapp-bot__chat-data .crm-whatsapp-bot__chat-data-etiqueta').val();
        let instance = $('.crm-whatsapp-bot__chat-data .crm-whatsapp-bot__chat-data-instance').val();
        console.log(instance)
        $.ajax({
            url: `/wp-json/crm-whatsapp-bot/v1/get_data?type=update_user`,
            method: 'GET',
            data: {
                user_id: userId,
                name: name,
                email: email,
                billing_phone: billing_phone,
                role: role,
                crm_etiqueta: crm_etiqueta,
                instance: instance
            },
            success: function(response) {
                console.log('[CRM] User data updated successfully:', response);
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: 'Datos del usuario actualizados correctamente.',
                });
                loadChats()
            },
            error: function(error) {
                console.error('[CRM] Error updating user data:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al actualizar los datos del usuario.',
                });
            }
        });


    });
    

    // Event listener for ESC key
    $(document).keydown(function(e) {
        if (e.keyCode == 27) {
            $('.crm-whatsapp-bot__chat-data').hide();
        }
    });

   // Send message button click handler
   $('.crm-whatsapp-bot__chat-footer').on('click', '.crm-whatsapp-bot__chat-send', function() {
       sendMessage();
   });

   $('.crm-whatsapp-bot__chat-input').on('keydown', function(e) {
        if (e.keyCode === 13 && !e.ctrlKey) {
            e.stopPropagation();
            e.preventDefault();

            var content = $(this).val();
            var caretPos = this.selectionStart;
            var newContent = content.substring(0, caretPos) + "\n" + content.substring(caretPos, content.length);
            $(this).val(newContent);
            // move caret to after inserted line break
            this.selectionStart = this.selectionEnd = caretPos + 1;

        } else  if (e.keyCode === 13 && e.ctrlKey) {
            sendMessage();
        }
   });

   function sendMessage() {
        const messageText = $('.crm-whatsapp-bot__chat-input').val();
        const userId = $('.crm-whatsapp-bot__chat-list li.active').data('user-id');
        const instance = $('.crm-whatsapp-bot__chat-list li.active').find('.crm-whatsapp-bot__chat-instance').text();
        
       if (!userId) {
           Swal.fire({
               icon: 'error',
               title: 'Error',
               text: 'No se ha seleccionado un chat.',
           });
           console.error('[CRM] User ID is not set.');
           return;
       }

        const messageData = {
            message: messageText,
            user_id: userId,
            instance: instance,
            media_id: mediaId
        };
        console.log(messageData)
        $('.crm-whatsapp-bot__chat-send').prop('disabled', true);

       $.ajax({
           url: '/wp-json/crm-whatsapp-bot/v1/send_message',
           method: 'POST',
           contentType: 'application/json',
           data: JSON.stringify(messageData),
           success: function(response) {
                console.log('[CRM] Message sent successfully:', response);

                // Update chat display
                const messageHtml = `<div class="crm-whatsapp-bot__message crm-whatsapp-bot__message--sent">${messageText}</div>`;
                $('.crm-whatsapp-bot__chat-body').append(messageHtml);

               // Clear input
                $('.crm-whatsapp-bot__chat-input').val('');
                mediaId = null
                $('.crm-whatsapp-bot__chat-send').prop('disabled', false);
               loadChats();
           },
           error: function(error) {
               console.error('[CRM] Error sending message:', error);
               Swal.fire({
                   icon: 'error',
                   title: 'Error al enviar el mensaje',
                   text: error.responseJSON.message,
               });
               $('.crm-whatsapp-bot__chat-send').prop('disabled', false);
           }
       });
   }
});
