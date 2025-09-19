<?php
header('Content-Type: application/json');

// Datos de noticias (puedes conectar esto a una base de datos en el futuro)
$news = [
    [
        "title" => "Descuento del 50% en productos seleccionados",
        "description" => "Aprovecha nuestras ofertas por tiempo limitado en productos seleccionados.",
        "url" => "shop.php",
    ],
    [
        "title" => "Nueva colección de relojes",
        "description" => "Descubre la nueva colección que acaba de llegar a nuestra tienda.",
        "url" => "shop.php",
    ],
    [
        "title" => "Anuncio de nuevos productos",
        "description" => "Mantente atento, nuevos productos estarán disponibles próximamente.",
        "url" => "shop.php",
    ],
    [
        "title" => "Entrega gratuita este fin de semana",
        "description" => "Compra ahora y disfruta de entrega gratuita en tus pedidos este fin de semana.",
        "url" => "shop.php",
    ],
    [
        "title" => "Únete a nuestra comunidad",
        "description" => "Regístrate en nuestro sitio web para recibir las últimas noticias y descuentos.",
        "url" => "user_register.php",
    ],
    [
        "title" => "Novedades en tecnología",
        "description" => "Explora nuestras últimas incorporaciones en gadgets y accesorios tecnológicos.",
        "url" => "shop.php",
    ],
    [
        "title" => "Lanzamiento de nueva app",
        "description" => "Descarga nuestra nueva app móvil para una mejor experiencia de compra.",
        "url" => "download_app.php",
    ],
    [
        "title" => "Eventos exclusivos para miembros",
        "description" => "Conviértete en miembro y accede a eventos y descuentos exclusivos.",
        "url" => "membership.php",
    ],
    [
        "title" => "Promoción de verano",
        "description" => "¡Prepárate para el verano con nuestra nueva línea de productos! Ofertas por tiempo limitado.",
        "url" => "summer_promo.php",
    ],
    [
        "title" => "Vacantes disponibles",
        "description" => "Únete a nuestro equipo. Consulta las vacantes disponibles y postúlate.",
        "url" => "careers.php",
    ],
];

// Enviar respuesta JSON
echo json_encode($news, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
