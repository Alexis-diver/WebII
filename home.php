<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
};

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>

    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">

    <style>
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
       .news-container::-webkit-scrollbar {
           width: 8px;
       }
       .news-container::-webkit-scrollbar-thumb {
           background: #ccc;
           border-radius: 4px;
       }
       .news-container::-webkit-scrollbar-track {
           background: #f1f1f1;
       }
       .news-card {
           padding: 10px;
           border-bottom: 1px solid #e0e0e0;
       }
       .news-card:last-child {
           border-bottom: none;
       }
       .news-card h3 {
           font-size: 16px;
           font-weight: bold;
           margin-bottom: 5px;
       }
       .news-card p {
           font-size: 14px;
           color: #555;
           margin-bottom: 10px;
       }
       .news-card a.btn {
           font-size: 14px;
           color: #007bff;
           text-decoration: none;
       }
       .news-card a.btn:hover {
           text-decoration: underline;
       }
    </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="home-bg">

<section class="home">

    <div class="swiper home-slider">
    
    <div class="swiper-wrapper">

       <div class="swiper-slide slide">
          <div class="image">
             <img src="images/icono.gif" alt="">
          </div>
          <div class="content">
             <span>Un gusto verte de</span>
             <h3>Bienvenido</h3>
          </div>
       </div>

    </div>

       <div class="swiper-pagination"></div>

    </div>

</section>

</div>

<section class="home-products">

    <h1 class="heading">Lista de productos</h1>

    <div class="swiper products-slider">

    <div class="swiper-wrapper">

    <?php
       $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 6"); 
       $select_products->execute();
       if ($select_products->rowCount() > 0) {
          while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <form action="" method="post" class="swiper-slide slide">
       <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
       <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
       <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
       <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
       <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
       <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
       <div class="name"><?= $fetch_product['name']; ?></div>
       <div class="flex">
          <div class="price">
             <span data-original-price="<?= $fetch_product['price']; ?>"><?= $fetch_product['price']; ?> MXN</span>
          </div>
          <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
       </div>
       <input type="submit" value="Add to Cart" class="btn" name="add_to_cart">
    </form>
    <?php
          }
       } else {
          echo '<p class="empty">Aún no hay ningún producto en venta</p>';
       }
    ?>

    </div>

    <div class="swiper-pagination"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>

    </div>

</section>

<section class="news-section">
    <h1 class="heading">Últimas Noticias</h1>
    <div class="news-container">
       </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>

<script>

// Configuración del carrusel de HOME (ESTE ES EL NUEVO BLOQUE)
var homeSwiper = new Swiper(".home-slider", {
    loop:true,
    spaceBetween: 30, // Espacio entre slides (opcional)
    effect: "fade", // Efecto de transición (opcional, puedes usar "slide", "cube", "coverflow", "flip")
    pagination: {
      el: ".home-slider .swiper-pagination", // Selector más específico para la paginación de este carrusel
      clickable: true,
    },
    autoplay: {
        delay: 7000, // Avanza automáticamente cada 7 segundos
        disableOnInteraction: false, // No detiene el autoplay al interactuar
    },
});


// Configuración del carrusel de productos
var productSwiper = new Swiper(".products-slider", {
    loop: true,
    spaceBetween: 20,
    navigation: {
       nextEl: '.products-slider .swiper-button-next', // Selector más específico
       prevEl: '.products-slider .swiper-button-prev', // Selector más específico
    },
    autoplay: {
       delay: 3000, // Avanza automáticamente cada 3 segundos
       disableOnInteraction: false, // No detiene el autoplay al interactuar
    },
    pagination: {
       el: ".products-slider .swiper-pagination", // Selector más específico para la paginación
       clickable: true,
    },
    breakpoints: {
       550: {
         slidesPerView: 2,
       },
       768: {
         slidesPerView: 2,
       },
       1024: {
         slidesPerView: 3,
       },
    },
});

// Cargar noticias desde el API
async function fetchNews() {
    try {
       const response = await fetch('news_api.php'); // Ruta del API
       const newsData = await response.json();
       const newsContainer = document.querySelector('.news-container');

       newsData.forEach(news => {
          const newsCard = document.createElement('div');
          newsCard.classList.add('news-card');
          newsCard.innerHTML = `
             <h3>${news.title}</h3>
             <p>${news.description}</p>
             <a href="${news.url}" class="btn" target="_blank">Leer Más</a>
          `;
          newsContainer.appendChild(newsCard);
       });
    } catch (error) {
       console.error('Error al cargar noticias:', error);
    }
}

// Cargar noticias al iniciar
document.addEventListener('DOMContentLoaded', () => {
    fetchNews();
});
</script>

</body>
</html>