<?php
include 'components/connect.php';

session_start();
$user_id = $_SESSION['user_id'] ?? '';

if (empty($user_id)) {
    header('location:login.php');
    exit();
}

$post_id = '';
$post = null;
$error_message = '';
$success_message = '';

if (isset($_POST['update_post'])) {
    $post_id = $_POST['post_id'];
    $text_content = filter_var($_POST['text_content'], FILTER_SANITIZE_STRING);

    // USANDO LA VISTA: Selecciona la publicación actual para obtener los datos multimedia
    $select_current_post = $conn->prepare("SELECT media_blob, media_type, media_filename FROM `user_posts_view` WHERE post_id = ? AND user_id = ?");
    $select_current_post->execute([$post_id, $user_id]);
    $current_post = $select_current_post->fetch(PDO::FETCH_ASSOC);

    $media_blob = $current_post['media_blob'];
    $media_type = $current_post['media_type'];
    $media_filename = $current_post['media_filename'];

    if (!empty($_FILES['new_media']['name'])) {
        $new_media = $_FILES['new_media'];
        $new_media_name = $new_media['name'];
        $new_media_tmp_name = $new_media['tmp_name'];
        $new_media_size = $new_media['size'];
        $new_media_error = $new_media['error'];

        $new_media_ext = strtolower(pathinfo($new_media_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi', 'mp3', 'wav'];

        if (in_array($new_media_ext, $allowed_extensions)) {
            if ($new_media_error === 0) {
                $media_blob = file_get_contents($new_media_tmp_name);

                if (in_array($new_media_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $media_type = 'image';
                } elseif (in_array($new_media_ext, ['mp4', 'mov', 'avi'])) {
                    $media_type = 'video';
                } elseif (in_array($new_media_ext, ['mp3', 'wav'])) {
                    $media_type = 'audio';
                }
                $media_filename = $new_media_name;
            } else {
                $error_message = 'Error al subir el nuevo archivo multimedia.';
            }
        } else {
            $error_message = 'Tipo de archivo no permitido para el nuevo medio.';
        }
    } elseif (isset($_POST['remove_media']) && $_POST['remove_media'] == '1') {
        $media_blob = null;
        $media_type = null;
        $media_filename = null;
    }

    if (empty($error_message)) {
        try {
            // La sentencia UPDATE sigue siendo directa en la tabla posts
            $update_post = $conn->prepare("UPDATE `posts` SET text_content = ?, media_blob = ?, media_type = ?, media_filename = ? WHERE post_id = ? AND user_id = ?");
            $update_post->bindParam(1, $text_content);
            $update_post->bindParam(2, $media_blob, PDO::PARAM_LOB);
            $update_post->bindParam(3, $media_type);
            $update_post->bindParam(4, $media_filename);
            $update_post->bindParam(5, $post_id);
            $update_post->bindParam(6, $user_id);
            $update_post->execute();

            $success_message = 'Publicación actualizada correctamente.';

            header('location:my_posts.php?message=' . urlencode($success_message));
            exit();

        } catch (PDOException $e) {
            error_log("Error al actualizar publicación: " . $e->getMessage());
            $error_message = 'Ocurrió un error al intentar actualizar la publicación.';
        }
    }
}

if (isset($_GET['post_id']) && !isset($_POST['update_post'])) {
    $post_id = $_GET['post_id'];
    try {
        // USANDO LA VISTA: Selecciona la publicación para la edición inicial
        $select_post = $conn->prepare("SELECT * FROM `user_posts_view` WHERE post_id = ? AND user_id = ?");
        $select_post->execute([$post_id, $user_id]);

        if ($select_post->rowCount() > 0) {
            $post = $select_post->fetch(PDO::FETCH_ASSOC);
        } else {
            $error_message = 'Publicación no encontrada o no tienes permiso para editarla.';
        }
    } catch (PDOException $e) {
        error_log("Error al cargar publicación para edición: " . $e->getMessage());
        $error_message = 'Ocurrió un error al cargar la publicación.';
    }
} elseif (!isset($_GET['post_id']) && !isset($_POST['update_post'])) {
    header('location:my_posts.php?error=ID de publicación no proporcionado para edición.');
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Publicación</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        .edit-post-form {
            max-width: 700px;
            margin: 30px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            font-family: 'Inter', sans-serif;
        }

        .edit-post-form .heading {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.8rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }

        .edit-post-form .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 1.1rem;
            text-align: center;
            color: #fff;
        }

        .edit-post-form .message.success {
            background-color: #28a745; 
        }

        .edit-post-form .message.error {
            background-color: #dc3545;
        }

        .edit-post-form .box {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1.1rem;
            color: #555;
            resize: vertical; 
            min-height: 120px;
        }

        .edit-post-form .flex {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .edit-post-form .input-group {
            margin-bottom: 20px;
        }

        .edit-post-form label {
            display: block;
            font-size: 1.1rem;
            color: #444;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .edit-post-form .current-media {
            margin-top: 15px;
            padding: 15px;
            border: 1px dashed #ccc;
            border-radius: 10px;
            background-color: #f5f5f5;
            text-align: center;
        }

        .edit-post-form .current-media p {
            font-size: 1rem;
            color: #777;
            margin-bottom: 10px;
        }

        .edit-post-form .current-media img,
        .edit-post-form .current-media video,
        .edit-post-form .current-media audio {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-top: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        .edit-post-form .checkbox-group {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .edit-post-form .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            width: 20px;
            height: 20px;
            accent-color: #dc3545; 
        }

        .edit-post-form .checkbox-group label {
            margin-bottom: 0;
            font-weight: normal;
            color: #666;
        }


        .edit-post-form .btn {
            display: block;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.2s ease;
            text-align: center;
            text-decoration: none; 
        }

        .edit-post-form .btn.submit {
            background-color: #007bff; 
            color: #fff;
        }

        .edit-post-form .btn.submit:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .edit-post-form .btn.cancel {
            background-color: #6c757d;
            color: #fff;
            margin-top: 15px;
        }

        .edit-post-form .btn.cancel:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .edit-post-form {
                margin: 20px;
                padding: 20px;
            }

            .edit-post-form .heading {
                font-size: 2.2rem;
            }

            .edit-post-form .box,
            .edit-post-form .btn {
                font-size: 1rem;
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            .edit-post-form {
                margin: 10px;
                padding: 15px;
            }

            .edit-post-form .heading {
                font-size: 1.8rem;
            }

            .edit-post-form .box,
            .edit-post-form .btn {
                font-size: 0.9rem;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<?php include 'components/user_social_header.php'; ?>

<section class="edit-post-form">
    <h1 class="heading">Editar Publicación</h1>

    <?php
    if (!empty($success_message)) {
        echo '<p class="message success">' . htmlspecialchars($success_message) . '</p>';
    }
    if (!empty($error_message)) {
        echo '<p class="message error">' . htmlspecialchars($error_message) . '</p>';
    }
    ?>

    <?php if ($post): ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['post_id']) ?>">
        <div class="input-group">
            <label for="text_content">Contenido de texto:</label>
            <textarea name="text_content" id="text_content" class="box" placeholder="Escribe tu publicación aquí..." required><?= htmlspecialchars($post['text_content']) ?></textarea>
        </div>

        <?php if ($post['media_blob']): ?>
            <div class="current-media">
                <p>Medio actual:</p>
                <?php
                    $mime = match ($post['media_type']) {
                        'image' => 'image/jpeg',
                        'video' => 'video/mp4',
                        'audio' => 'audio/mpeg',
                        default => ''
                    };
                    $data = base64_encode($post['media_blob']);
                ?>
                <?php if ($post['media_type'] === 'image'): ?>
                    <img src="data:<?= $mime ?>;base64,<?= $data ?>" alt="Medio actual">
                <?php elseif ($post['media_type'] === 'video'): ?>
                    <video controls>
                        <source src="data:<?= $mime ?>;base64,<?= $data ?>" type="<?= $mime ?>">
                        Tu navegador no soporta la etiqueta de video.
                    </video>
                <?php elseif ($post['media_type'] === 'audio'): ?>
                    <audio controls>
                        <source src="data:<?= $mime ?>;base64,<?= $data ?>" type="<?= $mime ?>">
                        Tu navegador no soporta la etiqueta de audio.
                    </audio>
                <?php endif; ?>
                <div class="checkbox-group">
                    <input type="checkbox" id="remove_media" name="remove_media" value="1">
                    <label for="remove_media">Eliminar medio actual</label>
                </div>
            </div>
        <?php endif; ?>

        <div class="input-group">
            <label for="new_media">Subir nuevo medio (opcional):</label>
            <input type="file" name="new_media" id="new_media" class="box" accept="image/*,video/*,audio/*">
        </div>

        <input type="submit" value="Actualizar Publicación" name="update_post" class="btn submit">
        <a href="my_posts.php" class="btn cancel">Cancelar</a>
    </form>
    <?php else: ?>
        <p class="empty">No se pudo cargar la publicación para edición.</p>
        <a href="my_posts.php" class="btn cancel">Volver a Mis Publicaciones</a>
    <?php endif; ?>
</section>

<?php include 'components/footer.php'; ?>

</body>
</html>