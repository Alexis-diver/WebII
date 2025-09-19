<?php

include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';

header('Content-Type: application/json');

if (empty($user_id) || !isset($_GET['receiver_id'])) {
    echo json_encode(['error' => 'Acceso denegado o ID de destinatario no proporcionado.']);
    exit();
}

$receiver_id = $_GET['receiver_id'];
$last_message_id = $_GET['last_message_id'] ?? 0; 

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
            um.sent_at ASC
    ");
    $select_messages->execute([$user_id, $receiver_id, $receiver_id, $user_id, $last_message_id]);
    $messages = $select_messages->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['messages' => $messages]);

} catch (PDOException $e) {
    error_log("Error al cargar mensajes en fetch_messages.php: " . $e->getMessage());
    echo json_encode(['error' => 'Ocurrió un error al cargar los mensajes.']);
}
?>
