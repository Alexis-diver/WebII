<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

include 'components/wishlist_cart.php';

$categories_query = $conn->prepare("SELECT * FROM `all_categories_view`");
$categories_query->execute();
$categories = $categories_query->fetchAll(PDO::FETCH_ASSOC);

// Handle search and category filter
$search_query = isset($_GET['search']) ? filter_var($_GET['search'], FILTER_SANITIZE_STRING) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : null;

$product_query = "SELECT * FROM `all_products_view`";
$params = [];

if ($search_query) {
    $product_query .= " WHERE name LIKE ?";
    $params[] = "%$search_query%";
}

if ($category_filter) {
    $product_query .= $search_query ? " AND" : " WHERE";
    // Assuming 'category' column exists in products table and matches category_id
    $product_query .= " category_id = ?"; 
    $params[] = $category_filter;
}

$select_products = $conn->prepare($product_query);
$select_products->execute($params);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscador</title>

    <!-- font awesome cdn link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="search-form">
    <form action="" method="get">
        <input type="text" name="search" placeholder="Busca un producto o usuario" maxlength="100" class="box" value="<?= htmlspecialchars($search_query); ?>">
        <button type="submit" class="fas fa-search"></button>
    </form>
</section>

<!-- Category Filter -->
<section class="categories">
    <h2>Filtrar por categorías</h2>
    <form method="get">
        <?php if ($search_query): ?>
            <input type="hidden" name="search" value="<?= htmlspecialchars($search_query); ?>">
        <?php endif; ?>
        <label>
            <input type="radio" name="category" value="" <?= is_null($category_filter) ? 'checked' : ''; ?> onchange="this.form.submit();">
            Todas las categorías
        </label>
        <br>
        <?php foreach ($categories as $category): ?>
            <label>
                <input type="radio" name="category" value="<?= $category['id'] ?>" <?= $category_filter === $category['id'] ? 'checked' : ''; ?> onchange="this.form.submit();">
                <?= htmlspecialchars($category['name']); ?>
            </label>
            <br>
        <?php endforeach; ?>
    </form>
</section>

<section class="products" style="padding-top: 0; min-height:100vh;">
    <div class="box-container">
        <?php
        if ($select_products->rowCount() > 0) {
            while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <form action="" method="post" class="box">
            <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
            <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
            <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
            <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
            <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
            <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
            <div class="name"><?= $fetch_product['name']; ?></div>
            <div class="flex">
                <div class="price"><span>$</span><?= $fetch_product['price']; ?><span>/-</span></div>
                <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
            </div>
            <input type="submit" value="Agregar al carrito" class="btn" name="add_to_cart">
        </form>
        <?php
            }
        } else {
        }
        ?>
    </div>
</section>

<!-- Search for Users -->
<section class="products" style="padding-top: 0; min-height:100vh;">
    <div class="box-container">
        <?php
        if ($search_query) {
        $select_users = $conn->prepare("SELECT * FROM `all_users_view` WHERE name LIKE ?");
        $select_users->execute(["%$search_query%"]);
            if ($select_users->rowCount() > 0) {
                while ($fetch_users = $select_users->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <form action="" method="post" class="box">
            <input type="hidden" name="name" value="<?= $fetch_users['name']; ?>">
            <div class="name"><?= $fetch_users['name']; ?></div>
            <input type="hidden" name="email" value="<?= $fetch_users['email']; ?>">
            <div class="email"><?= $fetch_users['email']; ?></div>
            <a href="user_profile.php?uid=<?= $fetch_users['id']; ?>" class="fas fa-eye"></a>
        </form>
        <?php
                }
            } else {
                echo '<p class="empty">No se encontraron usuarios con ese nombre.</p>';
            }
        }
        ?>
    </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>

</body>
</html>
