<?php
include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? '';

if (empty($user_id)) {
    header('location:login.php');
    exit();
}

$post_id = $_GET['post_id'] ?? '';
$post_details = null;
$comments = [];
$error_message = '';
$success_message = '';

if (empty($post_id)) {
    header('location:my_posts.php?error=ID de publicación no proporcionado.');
    exit();
}

if (isset($_POST['add_comment'])) {
    $comment_text = filter_var($_POST['comment_text'], FILTER_SANITIZE_STRING);

    if (!empty($comment_text)) {
        try {
            $insert_comment = $conn->prepare("INSERT INTO `comments` (post_id, user_id, comment_text) VALUES (?, ?, ?)");
            $insert_comment->execute([$post_id, $user_id, $comment_text]);
            $success_message = 'Comentario añadido correctamente.';
            // Redirige para limpiar el POST y mostrar el mensaje
            header('location:comments.php?post_id=' . $post_id . '&message=' . urlencode($success_message));
            exit();
        } catch (PDOException $e) {
            error_log("Error al añadir comentario: " . $e->getMessage());
            $error_message = 'Ocurrió un error al añadir el comentario.';
        }
    } else {
        $error_message = 'El comentario no puede estar vacío.';
    }
}

// Obtener detalles de la publicación usando la vista
try {
    $select_post = $conn->prepare("SELECT * FROM `v_social_posts` WHERE post_id = ?");
    $select_post->execute([$post_id]);
    $post_details = $select_post->fetch(PDO::FETCH_ASSOC);

    if ($post_details) {
        // Obtener comentarios de la publicación
        $select_comments = $conn->prepare("SELECT c.*, u.name FROM `comments` c JOIN `users` u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
        $select_comments->execute([$post_id]);
        $comments = $select_comments->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error al cargar publicación o comentarios: " . $e->getMessage());
    $error_message = 'Ocurrió un error al cargar los detalles de la publicación.';
}

// Manejar mensajes después de la redirección
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
    <title>Comentarios - Publicación</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        /* Estilos generales para la sección de comentarios */
        .comments-section {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .comments-section .heading {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .comments-section .post-display {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .comments-section .post-display .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .comments-section .post-display .post-header .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }

        .comments-section .post-display .post-header .username {
            font-weight: bold;
            color: #333;
            margin-right: 10px;
        }

        .comments-section .post-display .post-header .post-date {
            font-size: 0.9em;
            color: #888;
        }

        .comments-section .post-display p {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .comments-section .post-display .post-media {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-top: 10px;
            display: block;
        }

        .comments-section .post-display .post-actions {
            margin-top: 15px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .comments-section .post-display .post-actions a {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 0.95rem;
        }

        .comments-section .post-display .post-actions a i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        /* Colores de los botones para comentarios (consistentes con HomeSocial) */
        .comments-section .post-display .post-actions a.like-btn { background-color: #e0f7fa; color: #007bff; }
        .comments-section .post-display .post-actions a.like-btn:hover { background-color: #b3e5fc; color: #0056b3; }
        .comments-section .post-display .post-actions a.comment-btn { background-color: #e8f5e9; color: #28a745; }
        .comments-section .post-display .post-actions a.comment-btn:hover { background-color: #c8e6c9; color: #1e7e34; }
        .comments-section .post-display .post-actions a.edit-btn { background-color: #fff3e0; color: #ff9800; }
        .comments-section .post-display .post-actions a.edit-btn:hover { background-color: #ffe0b2; color: #ef6c00; }
        .comments-section .post-display .post-actions a.delete-btn { background-color: #ffebee; color: #f44336; }
        .comments-section .post-display .post-actions a.delete-btn:hover { background-color: #ffcdd2; color: #d32f2f; }


        .comments-section .add-comment-form {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .comments-section .add-comment-form h2 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .comments-section .add-comment-form textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            margin-bottom: 15px;
            resize: vertical;
        }

        .comments-section .add-comment-form .btn {
            display: block;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .comments-section .add-comment-form .btn:hover {
            background-color: #218838;
        }

        .comments-section .comments-list {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .comments-section .comments-list h2 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .comments-section .comment-card {
            background-color: #f8f8f8;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border: 1px solid #eee;
        }

        .comments-section .comment-card .comment-author {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .comments-section .comment-card .comment-text {
            color: #666;
            line-height: 1.5;
            margin-bottom: 5px;
            font-size: 0.95rem;
        }

        .comments-section .comment-card .comment-date {
            font-size: 0.85rem;
            color: #999;
            text-align: right;
        }

        .comments-section .empty {
            text-align: center;
            padding: 20px;
            font-size: 1.2rem;
            color: #888;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            margin-top: 20px;
        }

        .comments-section .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .comments-section .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .comments-section .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .comments-section .btn.cancel {
            display: block;
            width: fit-content;
            margin: 20px auto;
            background-color: #6c757d;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .comments-section .btn.cancel:hover {
            background-color: #5a6268;
        }

        /* Media Queries for responsiveness */
        @media (max-width: 768px) {
            .comments-section {
                padding: 15px;
                margin: 15px;
            }

            .comments-section .heading {
                font-size: 2rem;
            }

            .comments-section .post-display p,
            .comments-section .comment-card .comment-text {
                font-size: 1rem;
            }

            .comments-section .post-display .post-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .comments-section .post-display .post-actions a {
                margin-bottom: 10px;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .comments-section {
                padding: 10px;
                margin: 10px;
            }

            .comments-section .heading {
                font-size: 1.8rem;
            }

            .comments-section .post-display,
            .comments-section .add-comment-form,
            .comments-section .comments-list {
                padding: 15px;
            }

            .comments-section .post-display h3 {
                font-size: 1.4rem;
            }

            .comments-section .post-display p {
                font-size: 0.95rem;
            }

            .comments-section .add-comment-form h2,
            .comments-section .comments-list h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>

<?php include 'components/user_social_header.php'; ?>

<section class="comments-section">
    <h1 class="heading">Comentarios de la Publicación</h1>

    <?php
    if (!empty($success_message)) {
        echo '<p class="message success">' . htmlspecialchars($success_message) . '</p>';
    }
    if (!empty($error_message)) {
        echo '<p class="message error">' . htmlspecialchars($error_message) . '</p>';
    }
    ?>

    <?php if ($post_details): ?>
        <div class="post-display">
            <div class="post-header">
                <img src="https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/626fd8140423801.6241b91e24d9c.png" alt="Perfil" class="profile-pic">
                <span class="username"><?= htmlspecialchars($post_details['user_name']) ?></span>
                <span class="post-date"><?= date('d M Y, H:i', strtotime($post_details['created_at'])) ?></span>
            </div>
            <p><?= nl2br(htmlspecialchars($post_details['text_content'])) ?></p>
            <?php if (!empty($post_details['media_blob'])): ?>
                <?php
                    $mime = match ($post_details['media_type']) {
                        'image' => 'image/jpeg',
                        'video' => 'video/mp4',
                        'audio' => 'audio/mpeg',
                        default => ''
                    };
                    $data = base64_encode($post_details['media_blob']);
                ?>
                <?php if ($post_details['media_type'] === 'image'): ?>
                    <img src="data:<?= $mime ?>;base64,<?= $data ?>" alt="Publicación de imagen" class="post-media">
                <?php elseif ($post_details['media_type'] === 'video'): ?>
                    <video controls class="post-media">
                        <source src="data:<?= $mime ?>;base64,<?= $data ?>" type="<?= $mime ?>">
                        Tu navegador no soporta la etiqueta de video.
                    </video>
                <?php elseif ($post_details['media_type'] === 'audio'): ?>
                    <audio controls class="post-media">
                        <source src="data:<?= $mime ?>;base64,<?= $data ?>" type="<?= $mime ?>">
                        Tu navegador no soporta la etiqueta de audio.
                    </audio>
                <?php endif; ?>
            <?php endif; ?>
            <div class="post-actions">
                <a href="like_post.php?post_id=<?= $post_details['post_id'] ?>" class="like-btn"><i class="fas fa-heart"></i> <?= $post_details['like_count'] ?? 0 ?> Me gusta</a>
                <a href="comments.php?post_id=<?= $post_details['post_id'] ?>" class="comment-btn"><i class="fas fa-comment"></i> <?= $post_details['comment_count'] ?? 0 ?> Comentar</a>
                <?php if ($post_details['user_id'] == $user_id): ?>
                    <a href="edit_post.php?post_id=<?= $post_details['post_id'] ?>" class="edit-btn"><i class="fas fa-edit"></i> Editar</a>
                    <a href="delete_post.php?post_id=<?= $post_details['post_id'] ?>" class="delete-btn" onclick="return confirm('¿Estás seguro de que quieres eliminar esta publicación?');"><i class="fas fa-trash-alt"></i> Eliminar</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="add-comment-form">
            <h2>Añadir un Comentario</h2>
            <form action="comments.php?post_id=<?= $post_id ?>" method="POST">
                <textarea name="comment_text" placeholder="Escribe tu comentario aquí..." required></textarea>
                <input type="submit" value="Publicar Comentario" name="add_comment" class="btn">
            </form>
        </div>

        <div class="comments-list">
            <h2>Comentarios</h2>
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-card">
                        <p class="comment-author"><?= htmlspecialchars($comment['name']) ?></p>
                        <p class="comment-text"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></p>
                        <p class="comment-date"><?= date('d M Y, H:i', strtotime($comment['created_at'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty">Sé el primero en comentar.</p>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <p class="empty">No se pudo cargar la publicación o no existe.</p>
        <a href="my_posts.php" class="btn cancel">Volver a Mis Publicaciones</a>
    <?php endif; ?>
</section>

<?php include 'components/footer.php'; ?>
</body>
</html>