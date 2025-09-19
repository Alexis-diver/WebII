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
        $select_post = $conn->prepare("SELECT * FROM `posts` WHERE post_id = ? AND user_id = ?");
        $select_post->execute([$post_id, $user_id]);

        if ($select_post->rowCount() > 0) {
            $delete_post = $conn->prepare("DELETE FROM `posts` WHERE post_id = ? AND user_id = ?");
            $delete_post->execute([$post_id, $user_id]);

            header('location:my_posts.php?message=Publicación eliminada correctamente.');
            exit();
        } else {
            header('location:my_posts.php?error=No se pudo eliminar la publicación o no tienes permiso.');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error al eliminar publicación: " . $e->getMessage());
        header('location:my_posts.php?error=Ocurrió un error al intentar eliminar la publicación.');
        exit();
    }
} else {
    header('location:my_posts.php?error=ID de publicación no proporcionado.');
    exit();
}
?>
