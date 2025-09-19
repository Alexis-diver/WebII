<?php
include 'components/connect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? '';
if ($user_id == '') {
    header('location:user_login.php');
    exit;
}

$message = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text_content = trim($_POST['text_content']);
    $media_type = null;
    $media_blob = null;
    $media_filename = '';

    if (!empty($_FILES['media']['tmp_name'])) {
        $file_tmp = $_FILES['media']['tmp_name'];
        $file_type = $_FILES['media']['type'];
        $file_name = $_FILES['media']['name'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'video/mp4'];

        if (in_array($file_type, $allowed_types)) {
            if (str_starts_with($file_type, 'image/')) {
                $media_type = 'image';
            } elseif (str_starts_with($file_type, 'video/')) {
                $media_type = 'video';
            }

            $media_blob = file_get_contents($file_tmp);
            $media_filename = $file_name;
        } else {
            $message[] = 'Solo se permiten imágenes o videos válidos.';
        }
    }

    if (empty($message)) {
        $insert_post = $conn->prepare("INSERT INTO posts (user_id, text_content, media_type, media_blob, media_filename) VALUES (?, ?, ?, ?, ?)");
        $insert_post->bindParam(1, $user_id);
        $insert_post->bindParam(2, $text_content);
        $insert_post->bindParam(3, $media_type);
        $insert_post->bindParam(4, $media_blob, PDO::PARAM_LOB);
        $insert_post->bindParam(5, $media_filename);
        $insert_post->execute();

        $message[] = '¡Publicación realizada!';
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
   <title>Nueva Publicación</title>
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>

<?php include 'components/user_social_header.php'; ?>

<section class="new-post">
   <h1 class="heading">Crear publicación</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <textarea name="text_content" placeholder="¿Qué estás pensando?" required></textarea>

      <input type="file" name="media" accept="image/*,video/*">

      <input type="submit" value="Publicar" class="btn">
   </form>

   <?php
   if (!empty($message)) {
      foreach ($message as $msg) {
         echo '<p class="message">' . htmlspecialchars($msg) . '</p>';
      }
   }
   ?>
</section>

<style>
.new-post {
   max-width: 600px;
   margin: auto;
   padding: 20px;
}
.new-post form {
   display: flex;
   flex-direction: column;
   gap: 1rem;
}
.new-post textarea {
   width: 100%;
   height: 100px;
   resize: none;
   padding: 10px;
}
</style>

</body>
</html>
