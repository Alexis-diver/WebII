<?php
// Este bloque PHP asume que $conn y $user_id están disponibles si se incluye este archivo.
// Si es la página principal, session_start() y connect.php ya deberían haberse incluido.
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

        <a href="home.php" class="logo">AMAZING<span>.</span></a>

        <nav class="navbar">
            <a href="home.php">Inicio</a>
            <a href="orders.php">Pedidos</a>
            <a href="shop.php">Productos</a>
            <a href="pendings.php">Mensajes</a>
            <a href="homeSocial.php">Social</a>
        </nav>

        <div class="icons">
            <?php
            if (isset($conn) && isset($user_id) && $user_id != '') {
                $count_wishlist_items = $conn->prepare("SELECT * FROM `user_lists` WHERE user_id = ?");
                $count_wishlist_items->execute([$user_id]);
                $total_wishlist_counts = $count_wishlist_items->rowCount();

                $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $count_cart_items->execute([$user_id]);
                $total_cart_counts = $count_cart_items->rowCount();
            } else {
                $total_wishlist_counts = 0;
                $total_cart_counts = 0;
            }
            ?>
            <div id="menu-btn" class="fas fa-bars"></div>
            <a href="search_page.php"><i class="fas fa-search"></i></a>
            <a href="lists.php"><i class="fas fa-heart"></i><span class="wishlist-count"></span></a>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i><span class="cart-count">(<?= $total_cart_counts; ?>)</span></a>
            <div id="user-btn" class="fas fa-user"></div>
        </div>

        <div class="profile">
            <?php
            if (isset($conn) && isset($user_id) && $user_id != '') {
                $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
                $select_profile->execute([$user_id]);
                if ($select_profile->rowCount() > 0) {
                    $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
            ?>
                <p><?= htmlspecialchars($fetch_profile["name"]); ?></p>
                <a href="update_user.php" class="btn">Actualizar perfil</a>
                <div class="flex-btn">
                    <a href="user_register.php" class="option-btn">Registrarse</a>
                    <a href="user_login.php" class="option-btn">Iniciar sesión</a>
                </div>
                <a href="components/user_logout.php" class="delete-btn" onclick="return confirm('¿Cerrar sesión del sitio web?');">Cerrar sesión</a>
            <?php
                } else {
            ?>
                <p>Usuario no encontrado.</p>
                <div class="flex-btn">
                    <a href="user_register.php" class="option-btn">Registrarse</a>
                    <a href="user_login.php" class="option-btn">Iniciar sesión</a>
                </div>
            <?php
                }
            } else {
            ?>
                <p>¡Por favor inicia sesión o regístrate primero!</p>
                <div class="flex-btn">
                    <a href="user_register.php" class="option-btn">Registrarse</a>
                    <a href="user_login.php" class="option-btn">Iniciar sesión</a>
                </div>
            <?php
            }
            ?>
        </div>

        <div class="toggles">
            <div class="theme-toggle">
                <input type="checkbox" id="toggle-theme" />
                <label for="toggle-theme">Modo Oscuro</label>
            </div>
            <div class="price-toggle">
                <input type="checkbox" id="toggle-price" />
                <label for="toggle-price">USD</label>
            </div>
        </div>

    </section>

</header>

<?php
/*
NOTA: Si este archivo es un 'include' (header.php), el <style> y <script>
deberían estar en el archivo principal que lo incluye.
Este es el contenido que iría DENTRO de la etiqueta <style> en el <head> de tu página principal:
*/
?>
<style>
    /* ESTILOS DE NEWS-SECTION SE MANTIENEN COMO LOS TENÍAS */
    .news-section {
        margin: 20px auto;
        max-width: 800px;
        background: #f9f9f9;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
        overflow: hidden;
        position: relative;
    }
    .news-container {
        max-height: 200px;
        overflow-y: auto;
        padding: 10px;
    }
    .news-container::-webkit-scrollbar { width: 8px; }
    .news-container::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    .news-container::-webkit-scrollbar-track { background:rgb(129, 129, 129); }
    .news-card { padding: 10px; border-bottom: 1px solidrgb(204, 204, 204); }
    .news-card:last-child { border-bottom: none; }
    .news-card h3 { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
    .news-card p { font-size: 14px; color: #555; margin-bottom: 10px; }
    .news-card a.btn { font-size: 14px; color: #007bff; text-decoration: none; }
    .news-card a.btn:hover { text-decoration: underline; }

    /* === INICIO: ESTILOS HEADER - FONDO VAPORWAVE MÁS SUTIL === */
    .header {
        background-image: linear-gradient(135deg, 
            rgba(251, 243, 255, 0.85) 0%,  /* Lavanda suave con opacidad */
            rgba(214, 238, 253, 0.85) 40%, /* Azul cielo claro con opacidad */
            rgba(231, 249, 255, 0.85) 70%, /* Azul pálido/celeste con opacidad */
            rgba(255, 236, 239, 0.85) 100% /* Rosa claro con opacidad */
        );
        padding: 1.5rem 2rem;
        border-bottom: none; 
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15); /* Sombra más suave */
        position: sticky; 
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000; 
    }

    .header .flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap; 
    }

    /* Estilo general para texto y elementos del header para legibilidad */
    .header .logo,
    .header .navbar a,
    .header .icons i,
    .header .toggles label {
        color:rgb(97, 97, 97); /* Texto blanco */
        /* Sombra muy sutil para ayudar a despegar el texto del fondo si es necesario */
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3); 
    }

    .header .logo {
        font-size: 2.5rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .header .logo span { /* El punto en AMAZING. */
        /* Heredará el color blanco y la sombra. Si quieres un color específico: */
        /* color: rgba(255,255,255,0.8); */ /* Ejemplo: blanco un poco translúcido */
    }

    .header .navbar a {
        font-size: 1.6rem;
        text-transform: capitalize;
        margin: 0 0.5rem; 
        transition: color 0.2s ease, opacity 0.2s ease;
    }

    .header .navbar a:hover {
        color: #FFFFFF; 
        opacity: 0.8; /* Ligera transparencia al pasar el mouse */
    }

    .header .icons {
        display: flex;
        align-items: center;
        gap: 1.2rem; 
    }

    .header .icons i { 
        font-size: 1.8rem; 
        cursor: pointer;
        transition: color 0.2s ease, opacity 0.2s ease;
    }

    .header .icons i:hover {
        color: #FFFFFF; 
        opacity: 0.8;
    }

    /* Contador del carrito y lista de deseos */
    .header .icons a span.cart-count,
    .header .icons a span.wishlist-count {
        color: #333333; /* Texto oscuro para mejor contraste sobre fondo claro */
        background-color: rgba(255,255,255,0.7); /* Fondo blanco semi-transparente */
        border-radius: 10px; 
        padding: 0.2em 0.6em;
        font-size: 0.9rem;   
        position: relative;
        top: -9px; 
        left: -7px;
        font-weight: bold;
        text-shadow: none; 
        line-height: 1; 
        min-width: 18px; 
        text-align: center;
        display: inline-block; 
        border: 1px solid rgba(0,0,0,0.1); /* Borde sutil */
    }
     .header .icons a span.wishlist-count:empty { 
        display: none;
    }

    .header .profile {
        display: none; 
        position: absolute;
        top: 100%; 
        right: 2rem;
        background-color: rgba(40, 40, 50, 0.95); /* Un fondo oscuro pero ligeramente translúcido */
        border: 1px solid rgba(255,255,255,0.1); 
        border-radius: .5rem;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.3);
        padding: 1.5rem; 
        text-align: center;
        width: 30rem;
        z-index: 1100; 
    }
    .header .profile p {
        color: #E0E0E0; 
        margin-bottom: 1rem;
        font-size: 1.6rem;
    }
    .header .profile .btn, 
    .header .profile .option-btn, 
    .header .profile .delete-btn {
        margin-bottom: .75rem; 
    }
    .header .profile .flex-btn { 
        display: flex;
        justify-content: space-around;
        gap: 1rem; 
        margin-bottom: .75rem;
    }
    .header .profile .flex-btn .option-btn {
        flex: 1; 
        margin-bottom: 0; 
    }
     .header .profile .delete-btn {
        margin-bottom: 0; 
    }

    .header .toggles {
        display: flex;
        gap: 1.5rem;
        align-items: center;
    }
    /* Las etiquetas de los toggles ya tienen color blanco y sombra por la regla general */
    .header .toggles input[type="checkbox"] { 
        margin-right: 0.5rem;
        /* Usamos un color de acento más suave para el check del checkbox */
        accent-color: rgb(255, 230, 234); /* Rosa claro del degradado */
        cursor: pointer;
    }
    /* === FIN: ESTILOS HEADER === */
</style>

<script>
    // Cambio de tema claro/oscuro
    function toggleTheme() {
        const isDarkMode = document.getElementById('toggle-theme').checked;
        const newTheme = isDarkMode ? 'dark' : 'light';

        document.body.className = newTheme; // Aplica la clase al body
        localStorage.setItem('theme', newTheme);
    }

    // Cambio de moneda (MXN a USD)
    function toggleCurrency() {
        const isUSD = document.getElementById('toggle-price').checked;
        const currency = isUSD ? 'usd' : 'local';

        localStorage.setItem('currency', currency);

        const prices = document.querySelectorAll('.price span');
        const exchangeRate = 0.058; 

        prices.forEach(price => {
            const originalPrice = parseFloat(price.getAttribute('data-original-price'));
            if (!isNaN(originalPrice)) { 
                if (currency === 'usd') {
                    price.textContent = `$${(originalPrice * exchangeRate).toFixed(2)} USD`;
                } else {
                    price.textContent = `${originalPrice.toFixed(2)} MXN`;
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = localStorage.getItem('theme') || 'light'; 
        document.body.className = savedTheme;
        if (document.getElementById('toggle-theme')) {
            document.getElementById('toggle-theme').checked = savedTheme === 'dark';
            document.getElementById('toggle-theme').addEventListener('change', toggleTheme);
        }

        const savedCurrency = localStorage.getItem('currency') || 'local'; 
        if (document.getElementById('toggle-price')) {
            document.getElementById('toggle-price').checked = savedCurrency === 'usd';
            document.getElementById('toggle-price').addEventListener('change', toggleCurrency);
            toggleCurrency(); 
        }
        
        let profile = document.querySelector('.header .profile');
        if(document.querySelector('#user-btn')){
            document.querySelector('#user-btn').onclick = () =>{
                profile.classList.toggle('active');
                navbar.classList.remove('active'); 
            }
        }

        let navbar = document.querySelector('.header .navbar');
        if(document.querySelector('#menu-btn')){
            document.querySelector('#menu-btn').onclick = () =>{
                navbar.classList.toggle('active');
                profile.classList.remove('active'); 
            }
        }

        window.onscroll = () =>{ 
            navbar.classList.remove('active');
            profile.classList.remove('active');
        }

        // Debes tener estilos CSS para .active en .navbar y .profile para que se muestren
        // Ejemplo:
        // .header .navbar.active { display: flex; /* o block en móviles */ }
        // .header .profile.active { display: block; }
    });
</script>