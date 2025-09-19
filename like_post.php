<?php
include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';

if (empty($user_id)) {
    header('location:login.php');
    exit();
}

if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];

    try {
        $check_like = $conn->prepare("SELECT * FROM `likes` WHERE post_id = ? AND user_id = ?");
        $check_like->execute([$post_id, $user_id]);

        if ($check_like->rowCount() > 0) {
            $remove_like = $conn->prepare("DELETE FROM `likes` WHERE post_id = ? AND user_id = ?");
            $remove_like->execute([$post_id, $user_id]);
            $message = 'Me gusta quitado.';
        } else {
            $add_like = $conn->prepare("INSERT INTO `likes` (post_id, user_id) VALUES (?, ?)");
            $add_like->execute([$post_id, $user_id]);
            $message = 'Me gusta añadido.';
        }

        
        header('location:my_posts.php?message=' . urlencode($message));
        exit();

    } catch (PDOException $e) {
        error_log("Error al manejar like/unlike: " . $e->getMessage());
        header('location:my_posts.php?error=Ocurrió un error al procesar tu solicitud.');
        exit();
    }
} else {
    header('location:my_posts.php?error=ID de publicación no proporcionado.');
    exit();
}
?>
