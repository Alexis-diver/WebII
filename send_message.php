<?php
// Incluye el archivo de conexión a la base de datos
include 'components/connect.php';

// Inicia la sesión
session_start();

// Obtiene el ID del usuario de la sesión
$user_id = $_SESSION['user_id'] ?? '';

// Redirigir si el usuario no está logueado
if (empty($user_id)) {
    header('location:login.php');
    exit();
}

$receiver_id = $_GET['receiver_id'] ?? '';
$receiver_name = '';
$messages = [];
$error_message = '';
$success_message = '';

// Si no se proporciona un receiver_id, redirige de vuelta a la selección de usuarios
if (empty($receiver_id)) {
    header('location:messages.php?error=ID de destinatario no proporcionado.');
    exit();
}

// Obtener el nombre del destinatario
try {
    $select_receiver = $conn->prepare("SELECT name FROM `users` WHERE id = ?");
    $select_receiver->execute([$receiver_id]);
    if ($select_receiver->rowCount() > 0) {
        $receiver_name = $select_receiver->fetch(PDO::FETCH_ASSOC)['name'];
    } else {
        // Si el destinatario no se encuentra, redirige con un error
        header('location:messages.php?error=Destinatario no encontrado.');
        exit();
    }
} catch (PDOException $e) {
    // Manejo de errores de la base de datos al obtener el nombre del destinatario
    error_log("Error al obtener nombre del destinatario: " . $e->getMessage());
    $error_message = 'Ocurrió un error al cargar la información del destinatario.';
}


// Lógica para enviar un nuevo mensaje (ESTE BLOQUE PHP YA NO SERÁ EL PRINCIPAL PARA EL ENVÍO CON AJAX)
// Se mantiene como un fallback si JavaScript está deshabilitado.
if (isset($_POST['send_message_fallback'])) { // Cambiado el nombre del botón para evitar conflictos
    $message_text = filter_var($_POST['message_text'], FILTER_SANITIZE_STRING);

    if (!empty($message_text)) {
        try {
            $insert_message = $conn->prepare("INSERT INTO `user_messages` (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
            $insert_message->execute([$user_id, $receiver_id, $message_text]);
            $success_message = 'Mensaje enviado correctamente.';
            // Redirige para evitar el reenvío del formulario y limpiar el campo de texto
            header('location:send_message.php?receiver_id=' . $receiver_id . '&message=' . urlencode($success_message));
            exit();
        } catch (PDOException $e) {
            // Manejo de errores de la base de datos al enviar mensaje
            error_log("Error al enviar mensaje (fallback): " . $e->getMessage());
            $error_message = 'Ocurrió un error al enviar el mensaje.';
        }
    } else {
        $error_message = 'El mensaje no puede estar vacío.';
    }
}

// Obtener mensajes entre el usuario actual y el destinatario (carga inicial)
try {
    $select_messages = $conn->prepare("
    SELECT 
            * FROM 
            `v_user_conversations`
        WHERE 
            (sender_id = ? AND receiver_id = ?) 
            OR 
            (sender_id = ? AND receiver_id = ?)
        ORDER BY 
            sent_at ASC
    ");
    $select_messages->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
    $messages = $select_messages->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Manejo de errores de la base de datos al cargar mensajes
    error_log("Error al cargar mensajes (inicial): " . $e->getMessage());
    $error_message = 'Ocurrió un error al cargar los mensajes.';
}

// Muestra mensajes de éxito o error de la URL
if (isset($_GET['message'])) {
    $success_message = htmlspecialchars($_GET['message']);
}
if (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes con <?= htmlspecialchars($receiver_name) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        .chat-section {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-family: 'Inter', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 120px); 
        }

        .chat-section .heading {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-size: 2.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }

        .chat-section .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 1.1rem;
            text-align: center;
            color: #fff;
        }

        .chat-section .message.success {
            background-color: #28a745; 
        }

        .chat-section .message.error {
            background-color: #dc3545; 
        }

        .chat-box {
            flex-grow: 1;
            background-color: #e0e0e0;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow-y: auto; 
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 500px; 
            min-height: 150px;
        }

        .message-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 15px;
            word-wrap: break-word;
            font-size: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .message-bubble.sent {
            align-self: flex-end;
            background-color: #007bff;
            color: #fff;
            border-bottom-right-radius: 2px;
        }

        .message-bubble.received {
            align-self: flex-start; 
            background-color: #ffffff;
            color: #333;
            border-bottom-left-radius: 2px; 
        }

        .message-bubble .message-sender {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: rgba(255,255,255,0.8); 
        }

        .message-bubble.received .message-sender {
            color: #007bff; 
        }

        .message-bubble .message-time {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6); 
            margin-top: 5px;
            text-align: right;
        }

        .message-bubble.received .message-time {
            color: #777; 
        }

        .send-message-form {
            display: flex;
            gap: 10px;
            margin-top: auto; 
        }

        .send-message-form textarea {
            flex-grow: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 25px; 
            font-size: 1rem;
            resize: none; 
            max-height: 100px; 
            overflow-y: auto;
        }

        .send-message-form .btn {
            padding: 12px 25px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            white-space: nowrap;
        }

        .send-message-form .btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .back-button {
            display: block;
            width: fit-content;
            margin: 0 auto 20px auto;
            padding: 10px 20px;
            background-color: #6c757d;
            color: #fff;
            border-radius: 25px;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #5a6268;
        }

        @media (max-width: 768px) {
            .chat-section {
                padding: 15px;
                margin: 15px;
                min-height: calc(100vh - 100px);
            }

            .chat-section .heading {
                font-size: 2rem;
            }

            .chat-box {
                max-height: 400px;
            }

            .message-bubble {
                max-width: 85%;
                font-size: 0.95rem;
            }

            .send-message-form {
                flex-direction: column;
                gap: 15px;
            }

            .send-message-form textarea {
                max-height: 80px;
            }

            .send-message-form .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .chat-section {
                padding: 10px;
                margin: 10px;
                min-height: calc(100vh - 80px);
            }

            .chat-section .heading {
                font-size: 1.8rem;
            }

            .chat-box {
                max-height: 300px;
            }

            .message-bubble {
                font-size: 0.9rem;
            }

            .send-message-form textarea {
                padding: 10px;
            }

            .send-message-form .btn {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<?php include 'components/user_social_header.php'; ?>

<section class="chat-section">
    <h1 class="heading">Mensajes con <?= htmlspecialchars($receiver_name) ?></h1>

    <?php
    if (!empty($success_message)) {
        echo '<p class="message success">' . htmlspecialchars($success_message) . '</p>';
    }
    if (!empty($error_message)) {
        echo '<p class="message error">' . htmlspecialchars($error_message) . '</p>';
    }
    ?>

    <div class="chat-box" id="chatBox">
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message-bubble <?= ($msg['sender_id'] == $user_id) ? 'sent' : 'received' ?>" data-message-id="<?= $msg['message_id'] ?>">
                    <p class="message-sender"><?= htmlspecialchars($msg['sender_name']) ?></p>
                    <p class="message-text"><?= nl2br(htmlspecialchars($msg['message_text'])) ?></p>
                    <p class="message-time"><?= date('H:i', strtotime($msg['sent_at'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty" id="noMessagesText">No hay mensajes en esta conversación aún. ¡Sé el primero en enviar uno!</p>
        <?php endif; ?>
    </div>

    <form action="" method="POST" class="send-message-form" id="sendMessageForm">
        <textarea name="message_text" id="messageTextarea" placeholder="Escribe tu mensaje..." required></textarea>
        <input type="submit" value="Enviar" name="send_message" class="btn">
    </form>

    <a href="messages.php" class="back-button">Volver a Usuarios</a>
</section>

<?php include 'components/footer.php'; ?>

<script>
    const chatBox = document.getElementById('chatBox');
    const messageTextarea = document.getElementById('messageTextarea');
    const sendMessageForm = document.getElementById('sendMessageForm');
    const noMessagesText = document.getElementById('noMessagesText'); 

    let lastMessageId = 0; 

    function scrollToBottom() {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function renderMessage(msg, currentUserId) {
        const messageBubble = document.createElement('div');
        messageBubble.classList.add('message-bubble');
        messageBubble.classList.add((msg.sender_id == currentUserId) ? 'sent' : 'received');
        messageBubble.dataset.messageId = msg.message_id; 

        const senderName = document.createElement('p');
        senderName.classList.add('message-sender');
        senderName.textContent = msg.sender_name;

        const messageText = document.createElement('p');
        messageText.classList.add('message-text');
        messageText.innerHTML = msg.message_text.replace(/\n/g, '<br>'); 

        const messageTime = document.createElement('p');
        messageTime.classList.add('message-time');
        const date = new Date(msg.sent_at);
        messageTime.textContent = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        messageBubble.appendChild(senderName);
        messageBubble.appendChild(messageText);
        messageBubble.appendChild(messageTime);

        return messageBubble;
    }

    async function fetchMessages() {
        try {
            const receiverId = <?= json_encode($receiver_id) ?>;
            const currentUserId = <?= json_encode($user_id) ?>;
            
            const response = await fetch(`fetch_messages.php?receiver_id=${receiverId}&last_message_id=${lastMessageId}`);
            const data = await response.json();

            if (data.error) {
                console.error('Error al cargar mensajes:', data.error);
                return;
            }

            if (data.messages && data.messages.length > 0) {
                if (noMessagesText && noMessagesText.parentNode) { 
                    noMessagesText.parentNode.removeChild(noMessagesText);
                }

                data.messages.forEach(msg => {
                    if (!document.querySelector(`[data-message-id="${msg.message_id}"]`)) {
                        const messageElement = renderMessage(msg, currentUserId);
                        chatBox.appendChild(messageElement);
                        lastMessageId = Math.max(lastMessageId, msg.message_id); 
                    }
                });
                scrollToBottom();
            }
        } catch (error) {
            console.error('Error en la solicitud de mensajes:', error);
        }
    }


    sendMessageForm.addEventListener('submit', async function(event) {
        event.preventDefault(); 

        const messageText = messageTextarea.value.trim();
        if (messageText === '') {
            return; 
        }

        const receiverId = <?= json_encode($receiver_id) ?>;
        const currentUserId = <?= json_encode($user_id) ?>;

        try {
            const formData = new FormData();
            formData.append('message_text', messageText);
            formData.append('send_message_ajax', '1'); 
            formData.append('receiver_id', receiverId); 
            const response = await fetch('send_message_ajax.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                messageTextarea.value = ''; 
                fetchMessages(); 
            } else {
                console.error('Error al enviar mensaje:', data.error);
                
            }
        } catch (error) {
            console.error('Error en la solicitud de envío de mensaje:', error);

        }
    });

    window.addEventListener('load', () => {
        const existingMessages = chatBox.querySelectorAll('.message-bubble');
        if (existingMessages.length > 0) {
            lastMessageId = parseInt(existingMessages[existingMessages.length - 1].dataset.messageId);
        }
        scrollToBottom();
        setInterval(fetchMessages, 3000); //cada cuanto se actualiza 
    });

</script>
