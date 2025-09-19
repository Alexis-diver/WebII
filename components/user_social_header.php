<?php
if (isset($message)) {
    foreach ($message as $messageText) {
        echo '
        <div class="message">
            <span>' . htmlspecialchars($messageText) . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>';
    }
}
?>

<header class="header">
    <section class="flex">

        <!-- Logo -->
        <a href="homeSocial.php" class="logo">SOCIAL<span>.</span></a>

        <!-- Social Navigation -->
        <nav class="navbar">
            <a href="homeSocial.php">Inicio</a>
            <a href="new_post.php">Publicar</a>
            <a href="messages.php">Mensajes</a>
            <a href="my_posts.php">Mis publicaciones</a>
            <a href="home.php">Tienda</a>
        </nav>

        <!-- Icons Section -->
        <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <a href="search_social.php"><i class="fas fa-search"></i></a>
            <div id="user-btn" class="fas fa-user"></div>
        </div>

        <!-- Profile Info -->
        <div class="profile">
            <?php
            $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_profile->execute([$user_id]);
            if ($select_profile->rowCount() > 0) {
                $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
            ?>
                <p><?= htmlspecialchars($fetch_profile["name"]); ?></p>
                <a href="update_user.php" class="btn">Actualizar perfil</a>
                <a href="components/user_logout.php" class="delete-btn" onclick="return confirm('¿Cerrar sesión del sitio web?');">Cerrar sesión</a>
            <?php } ?>
        </div>

    </section>
</header>
