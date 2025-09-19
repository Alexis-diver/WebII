<?php
include 'components/connect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? '';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        /* Estilos generales para la sección de publicaciones */
        .social-feed {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .heading {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .feed-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Estilos para cada tarjeta de publicación */
        .post-card {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s ease-in-out;
        }

        .post-card:hover {
            transform: translateY(-5px);
        }

        /* --- INICIO: Nuevos estilos para el encabezado del post --- */
        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .post-header .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
        }

        .post-header .username {
            font-weight: bold;
            color: #333;
            margin-right: 10px;
        }

        .post-header .post-date {
            font-size: 0.9em;
            color: #888;
        }
        /* --- FIN: Nuevos estilos para el encabezado del post --- */

        .post-card h3 { /* Mantener si aún usas un h3 para el título del post */
            font-size: 1.8rem;
            color: #555;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .post-card p {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        /* Estilos para medios (imágenes, videos, audio) */
        .post-media { /* Clase unificada para medios */
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-top: 10px;
            display: block; /* Asegura que no haya espacio extra debajo de la imagen */
        }

        /* Estilos para las acciones de la publicación (likes, comentarios) */
        .post-actions {
            margin-top: 15px;
            display: flex;
            justify-content: flex-end; /* Alinea los botones a la derecha */
            gap: 15px; /* Espacio entre los botones */
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .post-actions a {
            display: inline-flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 0.95rem; /* Ajustar tamaño de fuente para los botones */
        }

        .post-actions a i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        /* --- INICIO: Colores específicos para los botones --- */
        .post-actions a.like-btn {
            background-color: #e0f7fa;
            color: #007bff;
        }
        .post-actions a.like-btn:hover {
            background-color: #b3e5fc;
            color: #0056b3;
        }

        .post-actions a.comment-btn {
            background-color: #e8f5e9;
            color: #28a745;
        }
        .post-actions a.comment-btn:hover {
            background-color: #c8e6c9;
            color: #1e7e34;
        }

        .post-actions a.edit-btn {
            background-color: #fff3e0; /* Amarillo suave */
            color: #ff9800; /* Naranja */
        }
        .post-actions a.edit-btn:hover {
            background-color: #ffe0b2;
            color: #ef6c00;
        }

        .post-actions a.delete-btn {
            background-color: #ffebee; /* Rojo suave */
            color: #f44336; /* Rojo */
        }
        .post-actions a.delete-btn:hover {
            background-color: #ffcdd2;
            color: #d32f2f;
        }
        /* --- FIN: Colores específicos para los botones --- */

        /* Estilo para el mensaje de "No hay publicaciones" */
        .empty {
            text-align: center;
            padding: 20px;
            font-size: 1.2rem;
            color: #888;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        /* Media Queries para responsividad */
        @media (max-width: 768px) {
            .social-feed {
                padding: 15px;
                margin: 15px;
            }

            .heading {
                font-size: 2rem;
            }

            .post-card h3 {
                font-size: 1.6rem;
            }

            .post-card p {
                font-size: 1rem;
            }

            .post-actions {
                flex-direction: column; /* Apila los botones en pantallas pequeñas */
                align-items: stretch; /* Estira los botones para ocupar todo el ancho */
            }

            .post-actions a {
                margin-right: 0;
                margin-bottom: 10px; /* Espacio entre botones apilados */
                justify-content: center; /* Centra el texto y el icono */
            }
        }

        @media (max-width: 480px) {
            .social-feed {
                padding: 10px;
                margin: 10px;
            }

            .heading {
                font-size: 1.8rem;
            }

            .post-card {
                padding: 15px;
            }

            .post-card h3 {
                font-size: 1.4rem;
            }

            .post-card p {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>

<?php include 'components/user_social_header.php'; ?>

<section class="social-feed">
    <h1 class="heading">Últimas Publicaciones</h1>

    <div class="feed-container">
    <?php
        // Consulta para seleccionar todas las publicaciones con conteo de likes y comentarios
        // Asume que 'v_social_posts' o la tabla 'posts' ya contiene
        // 'like_count' y 'comment_count' actualizados por los triggers.
        $select_posts = $conn->prepare("
            SELECT 
                p.*, 
                u.name AS user_name,
                p.like_count,    -- Obtener like_count directamente de posts
                p.comment_count  -- Obtener comment_count directamente de posts
            FROM 
                `posts` p
            JOIN 
                `users` u ON p.user_id = u.id 
            ORDER BY 
                p.created_at DESC
        ");
        $select_posts->execute();
        if ($select_posts->rowCount() > 0) {
            while ($post = $select_posts->fetch(PDO::FETCH_ASSOC)) {
                $mime = '';
                $data = '';

                if (!empty($post['media_blob'])) {
                    $mime = match ($post['media_type']) {
                        'image' => 'image/jpeg',
                        'video' => 'video/mp4',
                        'audio' => 'audio/mpeg',
                        default => ''
                    };
                    $data = base64_encode($post['media_blob']);
                }
    ?>
            <div class="post-card">
                <div class="post-header">
                    <img src="https://mir-s3-cdn-cf.behance.net/project_modules/max_1200/626fd8140423801.6241b91e24d9c.png" alt="Perfil" class="profile-pic"> <span class="username"><?= htmlspecialchars($post['user_name']) ?></span>
                    <span class="post-date"><?= date('d M Y, H:i', strtotime($post['created_at'])) ?></span>
                </div>
                <div class="post-content">
                    <p><?= nl2br(htmlspecialchars($post['text_content'])) ?></p>
                </div>

                <?php if (!empty($post['media_blob'])): ?>
                    <?php if ($post['media_type'] === 'image'): ?>
                        <img src="data:<?= $mime ?>;base64,<?= $data ?>" alt="Publicación de imagen" class="post-media">
                    <?php elseif ($post['media_type'] === 'video'): ?>
                        <video controls class="post-media">
                            <source src="data:<?= $mime ?>;base64,<?= $data ?>" type="<?= $mime ?>">
                            Tu navegador no soporta la etiqueta de video.
                        </video>
                    <?php elseif ($post['media_type'] === 'audio'): ?>
                        <audio controls class="post-media">
                            <source src="data:<?= $mime ?>;base64,<?= $data ?>" type="<?= $mime ?>">
                            Tu navegador no soporta la etiqueta de audio.
                        </audio>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="post-actions">
                    <a href="like_post.php?post_id=<?= $post['post_id'] ?>" class="like-btn"><i class="fas fa-heart"></i> <?= $post['like_count'] ?? 0 ?> Me gusta</a>
                    <a href="comments.php?post_id=<?= $post['post_id'] ?>" class="comment-btn"><i class="fas fa-comment"></i> <?= $post['comment_count'] ?? 0 ?> Comentar</a>
                    <?php if ($post['user_id'] == $user_id): ?>
                        <a href="edit_post.php?post_id=<?= $post['post_id'] ?>" class="edit-btn"><i class="fas fa-edit"></i> Editar</a>
                        <a href="delete_post.php?post_id=<?= $post['post_id'] ?>" class="delete-btn" onclick="return confirm('¿Estás seguro de que quieres eliminar esta publicación?');"><i class="fas fa-trash-alt"></i> Eliminar</a>
                    <?php endif; ?>
                </div>
            </div>
    <?php
            }
        } else {
            echo '<p class="empty">No hay publicaciones aún.</p>';
        }
    ?>
    </div>
</section>

<?php include 'components/footer.php'; ?>
</body>
</html>
