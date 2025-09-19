<?php
include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';

header('Content-Type: application/json');

if (empty($user_id)) {
    echo json_encode(['success' => false, 'error' => 'Acceso denegado: ID de usuario vacío.']);
    exit();
}

if (!isset($_POST['send_message_ajax'])) {
    echo json_encode(['success' => false, 'error' => 'Acceso denegado: Variable POST "send_message_ajax" no establecida.']);
    exit();
}

$receiver_id = $_POST['receiver_id'] ?? ''; 
$message_text = filter_var($_POST['message_text'] ?? '', FILTER_SANITIZE_STRING);

if (empty($receiver_id)) {
    echo json_encode(['success' => false, 'error' => 'ID de destinatario no proporcionado.']);
    exit();
}

if (empty($message_text)) {
    echo json_encode(['success' => false, 'error' => 'El mensaje no puede estar vacío.']);
    exit();
}

try {
    $insert_message = $conn->prepare("INSERT INTO `user_messages` (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
    $insert_message->execute([$user_id, $receiver_id, $message_text]);
    
    echo json_encode(['success' => true, 'message' => 'Mensaje enviado correctamente.']);
} catch (PDOException $e) {
    error_log("Error al enviar mensaje AJAX: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Ocurrió un error al enviar el mensaje.']);
}
?>
