<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}

// Add product with categories
if (isset($_POST['add_product'])) {

    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);

    $price = $_POST['price'];
    $price = filter_var($price, FILTER_SANITIZE_STRING);

    $details = $_POST['details'];
    $details = filter_var($details, FILTER_SANITIZE_STRING);

    $image_01 = $_FILES['image_01']['name'];
    $image_01 = filter_var($image_01, FILTER_SANITIZE_STRING);
    $image_tmp_name_01 = $_FILES['image_01']['tmp_name'];
    $image_folder_01 = '../uploaded_img/' . $image_01;

    $image_02 = $_FILES['image_02']['name'];
    $image_02 = filter_var($image_02, FILTER_SANITIZE_STRING);
    $image_tmp_name_02 = $_FILES['image_02']['tmp_name'];
    $image_folder_02 = '../uploaded_img/' . $image_02;

    $image_03 = $_FILES['image_03']['name'];
    $image_03 = filter_var($image_03, FILTER_SANITIZE_STRING);
    $image_tmp_name_03 = $_FILES['image_03']['tmp_name'];
    $image_folder_03 = '../uploaded_img/' . $image_03;

    $video = $_FILES['video']['name'];
    $video = filter_var($video, FILTER_SANITIZE_STRING);
    $video_tmp_name = $_FILES['video']['tmp_name'];
    $video_folder = '../uploaded_videos/' . $video;

    $quantity = $_POST['quantity'];
    $quantity = filter_var($quantity, FILTER_SANITIZE_STRING); // Consider FILTER_SANITIZE_NUMBER_INT if it's always a number

    $category_id_from_form = $_POST['category']; // This should be the category_id from the form
    $category_id_from_form = filter_var($category_id_from_form, FILTER_SANITIZE_NUMBER_INT);

    // Check if the product already exists
    $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
    $select_products->execute([$name]);

    if ($select_products->rowCount() > 0) {
        $message[] = '¡El nombre del producto ya existe!';
    } else {
        // Insert the new product along with the category ID
        // MODIFICACIÓN AQUÍ: 'category' cambiado a 'category_id'
        $insert_products = $conn->prepare("INSERT INTO `products` (name, details, price, image_01, image_02, image_03, video, quantity, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_products->execute([$name, $details, $price, $image_01, $image_02, $image_03, $video, $quantity, $category_id_from_form]);

        if ($insert_products) {
            // Move uploaded files
            // Es buena idea verificar si los archivos se subieron antes de intentar moverlos
            if ($image_01 != "" && file_exists($image_tmp_name_01)) {
                 move_uploaded_file($image_tmp_name_01, $image_folder_01);
            }
            if ($image_02 != "" && file_exists($image_tmp_name_02)) {
                move_uploaded_file($image_tmp_name_02, $image_folder_02);
            }
            if ($image_03 != "" && file_exists($image_tmp_name_03)) {
                move_uploaded_file($image_tmp_name_03, $image_folder_03);
            }
            if ($video != "" && file_exists($video_tmp_name)) {
                move_uploaded_file($video_tmp_name, $video_folder);
            }
            
            $message[] = '¡Nuevo producto agregado!';
        }
    }
}


if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_product_info = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
    $delete_product_info->execute([$delete_id]);
    if($delete_product_info->rowCount() > 0){ // Check if product exists before trying to fetch/unlink
        $fetch_delete_info = $delete_product_info->fetch(PDO::FETCH_ASSOC);
        if ($fetch_delete_info['image_01'] && file_exists('../uploaded_img/' . $fetch_delete_info['image_01'])) {
            unlink('../uploaded_img/' . $fetch_delete_info['image_01']);
        }
        if ($fetch_delete_info['image_02'] && file_exists('../uploaded_img/' . $fetch_delete_info['image_02'])) {
            unlink('../uploaded_img/' . $fetch_delete_info['image_02']);
        }
        if ($fetch_delete_info['image_03'] && file_exists('../uploaded_img/' . $fetch_delete_info['image_03'])) {
            unlink('../uploaded_img/' . $fetch_delete_info['image_03']);
        }
        if ($fetch_delete_info['video'] && file_exists('../uploaded_videos/' . $fetch_delete_info['video'])) {
            unlink('../uploaded_videos/' . $fetch_delete_info['video']);
        }
    }
    $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
    $delete_product->execute([$delete_id]);
    $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
    $delete_cart->execute([$delete_id]);
    $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
    $delete_wishlist->execute([$delete_id]);
    header('location:products.php');
    exit; // Good practice to exit after header redirect
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>products</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="add-products">

    <h1 class="heading">Agrega un producto</h1>

    <form action="" method="post" enctype="multipart/form-data">
        <div class="flex">
            <div class="inputBox">
                <span>Nombre del producto (requisito)</span>
                <input type="text" class="box" required maxlength="100" placeholder="Nombre del producto" name="name">
            </div>
            <div class="inputBox"> <span>Categoría (requisito)</span>
                <select name="category" id="category" class="box" required> <option value="">Seleccionar categoría</option>
                    <?php
                    $categories_query = $conn->prepare("SELECT * FROM `categories` ORDER BY name ASC"); // Added ORDER BY
                    $categories_query->execute();
                    if ($categories_query->rowCount() > 0) {
                        // Usamos $category_row para evitar conflicto con la variable $category_id_from_form de arriba
                        while ($category_row = $categories_query->fetch(PDO::FETCH_ASSOC)) {
                            // MODIFICACIÓN AQUÍ: Usamos 'category_id' y 'name' de tu tabla 'categories'
                            echo "<option value='{$category_row['category_id']}'>{$category_row['name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="inputBox">
                <span>Precio (opcional):</span>
                <input type="checkbox" id="priceCheckbox">
                <label for="priceCheckbox">Marcar casilla si se cotizara</label>
                <input type="number" id="priceInput" min="0" class="box" max="9999999999" placeholder="Precio" name="price">
            </div>
            <div class="inputBox">
                <span>Cantidad en stock (requisito)</span>
                <input type="number" min="0" class="box" required max="9999999999" placeholder="Cantidad" name="quantity"> </div>
            <div class="inputBox">
                <span>Imagen 01 (requisito)</span>
                <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
            <div class="inputBox">
                <span>Imagen 02 (requisito)</span>
                <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
            <div class="inputBox">
                <span>Imagen 03 (requisito)</span>
                <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
            </div>
            <div class="inputBox">
                <span>Video (requisito)</span> <input type="file" name="video" accept="video/mp4, video/webm, video/ogg" class="box" required>
            </div>
            <div class="inputBox">
                <span>Detalles del producto (requisitos)</span>
                <textarea name="details" placeholder="Detalles del producto" class="box" required maxlength="500" cols="30" rows="10"></textarea>
            </div>
        </div>
        
        <input type="submit" value="Agregar productos" class="btn" name="add_product">
    </form>

</section>

<section class="show-products">

    <h1 class="heading">Lista de productos</h1>

    <div class="box-container">
        <?php
        $select_products = $conn->prepare("SELECT p.*, c.name as category_name FROM `products` p LEFT JOIN `categories` c ON p.category_id = c.category_id ORDER BY p.name ASC"); // Modified to show category name
        $select_products->execute();
        if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="box">
            <?php if($fetch_products['image_01'] != ''){ ?>
                <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_01']); ?>" alt="">
            <?php } else { ?>
                <img src="../images/placeholder.png" alt=""> <?php } ?>
            <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>
            <div class="price">$<span><?= htmlspecialchars($fetch_products['price']); ?></span>/-</div>
            <?php if (isset($fetch_products['category_name'])) { ?>
                <div class="category">Categoría: <span><?= htmlspecialchars($fetch_products['category_name']); ?></span></div>
            <?php } ?>
            <div class="quantity">Stock: <span><?= htmlspecialchars($fetch_products['quantity']); ?></span></div>
            <div class="details"><span><?= htmlspecialchars($fetch_products['details']); ?></span></div>
            <div class="flex-btn">
                <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
                <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
            </div>
        </div>
        <?php
            }
        } else {
            echo '<p class="empty">Ningún producto agregado</p>';
        }
        ?>
    </div>

</section>

<script src="../js/admin_script.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const priceCheckbox = document.getElementById('priceCheckbox');
    const priceInput = document.getElementById('priceInput');

    // Function to toggle price input visibility and requirement
    function togglePriceInput() {
        if (priceCheckbox.checked) {
            priceInput.style.display = 'none';
            priceInput.removeAttribute('required'); // No requerido si se cotiza
            priceInput.value = ''; // Opcional: limpiar valor
        } else {
            priceInput.style.display = 'block';
            priceInput.setAttribute('required', 'required'); // Requerido si no se cotiza
        }
    }

    // Initial check on page load
    if (priceCheckbox && priceInput) {
         togglePriceInput(); // Call on load to set initial state

        // Add event listener
        priceCheckbox.addEventListener('change', togglePriceInput);
    } else {
        console.warn("Price checkbox or input not found.");
    }
});
</script>

</body>
</html>