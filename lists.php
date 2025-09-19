<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
    header('location:user_login.php');
    exit();
}

include 'components/wishlist_cart.php'; // Ensure this file exists and doesn't conflict

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_list'])) {
        $list_name = filter_var($_POST['list_name'], FILTER_SANITIZE_STRING);
        // Insert into the new user_list_headers table
        $create_list = $conn->prepare("INSERT INTO `user_list_headers` (user_id, name) VALUES (?, ?)");
        $create_list->execute([$user_id, $list_name]);
        $message[] = 'Lista creada con éxito!';
    }

    if (isset($_POST['delete_list'])) {
        $list_id = filter_var($_POST['list_id'], FILTER_SANITIZE_NUMBER_INT);
        // Delete from user_list_headers first (due to CASCADE, items will be deleted)
        // Ensure 'list_id' is the correct column name in user_list_headers for the primary key
        $delete_list = $conn->prepare("DELETE FROM `user_list_headers` WHERE list_id = ? AND user_id = ?");
        $delete_list->execute([$list_id, $user_id]);
        // If CASCADE is set on fk_list_item_header, the list_items will be deleted automatically.
        // If not, you'd need:
        // $delete_items = $conn->prepare("DELETE FROM `list_items` WHERE list_id = ?");
        // $delete_items->execute([$list_id]);
        $message[] = 'Lista eliminada!';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Listas</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Hexagonal Menu */
        .hex-menu {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            justify-items: center;
            align-items: center;
            margin: 30px auto;
            max-width: 600px;
        }

        .hex-item {
            position: relative;
            width: 110px;
            height: 110px;
            background-color: #007bff;
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 1.5rem;
            text-align: center;
            transition: transform 0.3s ease, background-color 0.3s ease;
            cursor: pointer;
        }

        .hex-item:hover {
            transform: scale(1.1);
            background-color: #0056b3;
        }

        .hex-item i {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .hex-item span {
            display: block;
            font-size: 1rem;
            text-transform: uppercase;
        }

        .hex-item.active {
            background-color: #ff9800;
        }

        /* Tab Content */
        .tab-content {
            display: none;
            margin-top: 20px;
        }

        .tab-content.active {
            display: block;
        }

        .wishlist-content {
            margin-top: 30px;
        }

        .btn {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .empty {
            text-align: center;
            font-size: 1.2rem;
            color: #666;
        }
    </style>
</head>
<body class="light">

<?php include 'components/user_header.php'; ?>

<section class="user-lists">
    <h1 class="heading">Mis Listas</h1>

    <form method="post" class="create-list-form">
        <input type="text" name="list_name" placeholder="Nombre de la nueva lista" required>
        <button type="submit" name="create_list" class="btn">Crear Lista</button>
    </form>

    <div class="lists-container">
        <?php
        // Fetch user lists from the new user_list_headers table
        $user_lists = $conn->prepare("SELECT * FROM `user_list_headers` WHERE user_id = ?");
        $user_lists->execute([$user_id]);
        $lists = $user_lists->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($lists)) {
            foreach ($lists as $list) {
                ?>
                <div class="list">
                    <h3><?= htmlspecialchars($list['name']); ?></h3>
                    <form method="post" class="delete-list-form">
                        <input type="hidden" name="list_id" value="<?= $list['list_id']; ?>"> <button type="submit" name="delete_list" class="btn delete-btn">Eliminar</button>
                    </form>
                    <a href="lists.php?list_id=<?= $list['list_id']; ?>" class="btn view-btn">Ver Productos</a>
                </div>
                <?php
            }
        } else {
            echo '<p>No tienes listas creadas.</p>';
        }
        ?>
    </div>
</section>

<section class="list-items">
    <h1 class="heading">Productos en la Lista</h1>

    <?php
    // Fetch items in the selected list from the (renamed) list_items table
    if (isset($_GET['list_id'])) {
        $list_id = filter_var($_GET['list_id'], FILTER_SANITIZE_NUMBER_INT);
        $list_items = $conn->prepare("
            SELECT p.*
            FROM `list_items` li -- Assuming you renamed user_lists to list_items
            JOIN `products` p ON li.product_id = p.id
            WHERE li.list_id = ?
        ");
        $list_items->execute([$list_id]);
        $products_in_list = $list_items->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($products_in_list)) {
            echo '<div class="products-container">';
            foreach ($products_in_list as $product) {
                ?>
                <div class="product">
                    <img src="uploaded_img/<?= htmlspecialchars($product['image_01']); ?>" alt="">
                    <h3><?= htmlspecialchars($product['name']); ?></h3>
                    <p><?= htmlspecialchars($product['details']); ?></p>
                    <p>Precio: $<?= htmlspecialchars($product['price']); ?></p>
                </div>
                <?php
            }
            echo '</div>';
        } else {
            echo '<p>Esta lista está vacía.</p>';
        }
    }
    ?>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const menuItems = document.querySelectorAll('.hex-item');
        const contents = document.querySelectorAll('.tab-content');

        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                menuItems.forEach(i => i.classList.remove('active'));
                item.classList.add('active');

                const tabId = item.getAttribute('data-tab');
                contents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === tabId) {
                        content.classList.add('active');
                    }
                });
            });
        });
    });
</script>

</body>
</html>