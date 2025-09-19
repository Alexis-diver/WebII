<?php
include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

// Check database connection
if (!isset($conn)) {
    die("Error: No se pudo establecer conexión con la base de datos.");
}

// Fetch user lists
$lists = [];
if (!empty($user_id)) {
    $list_query = $conn->prepare("SELECT * FROM `user_lists` WHERE user_id = ?");
    $list_query->execute([$user_id]);
    $lists = $list_query->fetchAll(PDO::FETCH_ASSOC);
}

// Add product to list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_list'])) {
    $list_id = isset($_POST['list_id']) ? intval($_POST['list_id']) : null;
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : null;

    if (!empty($list_id) && !empty($product_id)) {
        // Check if the product already exists in the list
        $check_query = $conn->prepare("SELECT * FROM `user_lists` WHERE list_id = ? AND product_id = ?"); // Changed table to user_lists
        $check_query->execute([$list_id, $product_id]);

        if ($check_query->rowCount() == 0) {
            $insert_query = $conn->prepare("INSERT INTO `user_lists` (list_id, user_id, product_id) VALUES (?, ?, ?)"); // Added user_id to insert
            $insert_query->execute([$list_id, $user_id, $product_id]); // Passed user_id
            $message[] = 'Producto agregado a la lista!';
        } else {
            $message[] = 'Este producto ya está en la lista!';
        }
    } else {
        $message[] = 'Por favor selecciona una lista válida.';
    }
}


// Add comments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = htmlspecialchars($_POST['comment']);
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($rating < 1 || $rating > 5) {
        echo "<script>alert('Por favor selecciona una calificación válida (1-5).');</script>";
    } elseif (empty($comment)) {
        echo "<script>alert('El comentario no puede estar vacío.');</script>";
    } else {
        // Use 'text' and 'rating' columns directly from your schema
        $insert_comment = $conn->prepare("INSERT INTO `comments` (product_id, user_id, text, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
        $insert_comment->execute([$product_id, $user_id, $comment, $rating]);
        echo "<script>alert('Comentario agregado con éxito.');</script>";
    }
}

// Fetch product details
$fetch_product = null;
if (isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);
    $product_query = "
        SELECT p.*, c.name AS category_name
        FROM `products` p
        LEFT JOIN `categories` c ON p.category_id = c.category_id -- CORRECTED LINE HERE
        WHERE p.id = ?";
    $select_products = $conn->prepare($product_query);
    $select_products->execute([$pid]);
    $fetch_product = $select_products->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">

    <style>
.quick-view {
    padding: 3rem;
    margin: 4rem auto;
    max-width: 1400px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.quick-view .heading {
    font-size: 3.5rem;
    color: #333;
    text-align: center;
    margin-bottom: 4rem;
    text-transform: uppercase;
    font-weight: bold;
}

.quick-view .box {
    display: flex;
    flex-wrap: wrap;
    gap: 2.5rem;
    background-color: #f9f9f9;
    border-radius: 10px;
    padding: 2.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.quick-view .image-container {
    flex: 1 1 60%;
    text-align: center;
}

.quick-view .image-container img {
    width: 100%;
    max-width: 500px;
    height: auto;
    border-radius: 8px;
}

.quick-view .content {
    flex: 1 1 35%;
    padding: 2rem;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.add-comment {
    margin: 3rem auto 4rem;
    padding: 2.5rem;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    text-align: center;
    max-width: 800px;
}

.add-comment textarea {
    width: 100%;
    padding: 1.5rem;
    font-size: 1.6rem;
    border-radius: 8px;
    border: 1px solid #ccc;
    resize: none;
    min-height: 150px;
}

.add-comment button {
    margin-top: 1.5rem;
    background-color: #2980b9;
    color: #fff;
    border: none;
    padding: 1.2rem 2.5rem;
    font-size: 1.6rem;
    border-radius: 8px;
    cursor: pointer;
}

.add-comment button:hover {
    background-color: #1c5d85;
}

.Reseñas {
    margin-top: 4rem;
    padding: 3rem;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.Reseñas .comment-box {
    padding: 2rem;
    margin-bottom: 2rem;
    background-color: #fff;
    border-radius: 8px;
    border: 1px solid #ddd;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.Reseñas h2 {
    font-size: 2.8rem;
    color: #333;
    text-align: center;
    margin-bottom: 2rem;
}

.quick-view .content .name {
    font-size: 2.8rem;
    color: #333;
    margin-bottom: 1rem;
}

.quick-view .content .price {
    font-size: 2.5rem;
    color: #e74c3c;
    margin-bottom: 1rem;
}

.quick-view .content .quantity {
    font-size: 2rem;
    color: #666;
    margin-bottom: 1.5rem;
}

.quick-view .content .details {
    font-size: 1.8rem;
    color: #555;
    line-height: 1.8;
    margin-bottom: 2rem;
}

.flex-btn {
    display: flex;
    gap: 1.5rem;
}

.flex-btn button {
    font-size: 1.6rem;
    padding: 1rem 2rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
}

.btn {
    background-color: #2980b9;
    color: #fff;
}

.btn:hover {
    background-color: #1c5d85;
}

.option-btn {
    background-color: #f39c12;
    color: #fff;
}

.option-btn:hover {
    background-color: #d68910;
}
</style>

</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="quick-view">
    <h1 class="heading">Vista Previa</h1>
    <?php
    if (isset($_GET['pid'])) {
        $pid = $_GET['pid'];
        // Using $fetch_product directly here, as it's populated earlier with the correct query
        // The original code had a redundant select_products here which fetched only product details
        // and didn't include category_name, causing the category display to fail if $fetch_product from above wasn't used.
        // We'll rely on the $fetch_product variable that already contains category_name.

        if ($fetch_product) { // Check if a product was actually found
            if (!empty($fetch_product['category_name'])) {
                echo "Categoría: " . htmlspecialchars($fetch_product['category_name']);
            } else {
                echo "Categoría: Sin categoría";
            }

            // Now, we display the product details using $fetch_product
    ?>
    <div class="box">

        <div class="image-container">
            <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01']); ?>" alt="Producto principal">
            <?php if (!empty($fetch_product['image_02'])): ?>
                <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_02']); ?>" alt="Producto adicional">
            <?php endif; ?>
            <?php if (!empty($fetch_product['image_03'])): ?>
                <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_03']); ?>" alt="Producto adicional">
            <?php endif; ?>
            <?php if (!empty($fetch_product['video'])): ?>
                <img src="uploaded_videos/<?= htmlspecialchars($fetch_product['video']); ?>" alt="Producto adicional">
            <?php endif; ?>
        </div>
        <div class="content">
            <div class="name"><?= htmlspecialchars($fetch_product['name']); ?></div>
            <div class="price" data-mxn-price="<?= $fetch_product['price']; ?>">
                <span>$</span><?= $fetch_product['price']; ?><span> MXN</span>
            </div>
            <div class="quantity">Stock disponible: <?= htmlspecialchars($fetch_product['quantity']); ?></div>
            <div class="details"><?= htmlspecialchars($fetch_product['details']); ?></div>

            <form method="post">
                <select name="list_id" required>
                    <option value="">Seleccionar lista</option>
                    <?php foreach ($lists as $list): ?>
                        <option value="<?= $list['list_id']; ?>"><?= htmlspecialchars($list['name']); ?></option> <?php endforeach; ?>
                </select>
                <input type="hidden" name="product_id" value="<?= $pid; ?>">
                <button type="submit" name="add_to_list" class="btn">Agregar a la Lista</button>
            </form>
        </div>
    </div>

    <form action="quotation_request.php" method="POST">
        <input type="hidden" name="product_id" value="<?= $fetch_product['id']; ?>">
        <label for="new_price">Ingrese su precio propuesto:</label>
        <input type="number" name="new_price" id="new_price" min="0" step="0.01" required>
        <button type="submit" name="request_quotation" class="btn">Solicitar Cotización</button>
    </form>


    <div class="add-comment">
        <form action="" method="post">
            <textarea name="comment" placeholder="Escribe tu comentario aquí..."></textarea>
            <input type="hidden" name="product_id" value="<?= $pid; ?>">
            <div>
                <label>
                    Calificación:
                    <select name="rating">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </label>
            </div>
            <button type="submit" name="comment">Enviar comentario</button> </form>
    </div>

    <div class="Reseñas">
        <h2>Comentarios y Reseñas</h2>
        <?php
        // Fetch comments using the correct column names from your schema
        $select_comments = $conn->prepare("
            SELECT c.comment_text, c.rating, c.created_at, u.name
            FROM `comments` c
            JOIN `users` u ON c.user_id = u.id
            WHERE c.product_id = ?
            ORDER BY c.created_at DESC
        ");
        $select_comments->execute([$pid]);

        if ($select_comments->rowCount() > 0) {
            while ($fetch_comment = $select_comments->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="comment-box">
            <p><strong><?= htmlspecialchars($fetch_comment['name']); ?>:</strong> <?= htmlspecialchars($fetch_comment['comment_text']); ?></p>
            <p>Rating: <?= htmlspecialchars($fetch_comment['rating']); ?> / 5</p>
            <span class="comment-date"><?= htmlspecialchars($fetch_comment['created_at']); ?></span>
        </div>
        <?php
            }
        } else {
            echo "<p>No hay comentarios todavía.</p>";
        }
        ?>
    </div>
    <?php
        } else {
            echo "<p>No se encontró el producto.</p>";
        }
    } else {
        echo "<p>No se proporcionó un ID de producto.</p>";
    }
    ?>
</section>

<?php include 'components/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // This element `toggle-price` doesn't exist in your HTML.
        // If you intend to have a currency toggle, you'll need to add an input element with id="toggle-price"
        // For now, I'm commenting this out to avoid a JavaScript error in the console.
        // const togglePrice = document.getElementById('toggle-price');
        const prices = document.querySelectorAll('.price');
        const exchangeRate = 18; // Example exchange rate

        // if (togglePrice) { // Check if togglePrice exists before adding listener
        //     togglePrice.addEventListener('change', () => {
        //         const isUSD = togglePrice.checked;
        //         prices.forEach(price => {
        //             const mxnPrice = parseFloat(price.dataset.mxnPrice);
        //             if (isUSD) {
        //                 price.innerHTML = `<span>$</span>${(mxnPrice / exchangeRate).toFixed(2)}<span> USD</span>`;
        //             } else {
        //                 price.innerHTML = `<span>$</span>${mxnPrice}<span> MXN</span>`;
        //             }
        //         });
        //     });
        // }
    });
</script>

</body>
</html>