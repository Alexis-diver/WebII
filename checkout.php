<?php

include 'components/connect.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$grand_total = 0; // Initialize grand total
$all_items = [];  // Initialize all items array
$total_products = ""; // Initialize total products description

// Fetch cart items
$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
$select_cart->execute([$user_id]);

while ($cart_item = $select_cart->fetch(PDO::FETCH_ASSOC)) {
    $all_items[] = [
        'name' => $cart_item['name'],
        'price' => $cart_item['price'],
        'quantity' => $cart_item['quantity'],
    ];
    $grand_total += $cart_item['price'] * $cart_item['quantity'];
}

// Fetch quotation items if a quotation ID is provided
if (isset($_GET['quotation_id'])) {
    $quotation_id = intval($_GET['quotation_id']);
    $fetch_quotation_items = $conn->prepare("
        SELECT qi.*, p.name, qi.new_price 
        FROM `quotation_items` qi 
        JOIN `products` p ON qi.product_id = p.id 
        WHERE qi.quotation_id = ?
    ");
    $fetch_quotation_items->execute([$quotation_id]);

    while ($quotation_item = $fetch_quotation_items->fetch(PDO::FETCH_ASSOC)) {
        $all_items[] = [
            'name' => $quotation_item['name'],
            'price' => $quotation_item['new_price'],
            'quantity' => $quotation_item['quantity'],
        ];
        $grand_total += $quotation_item['new_price'] * $quotation_item['quantity'];
    }
}

if (isset($_POST['order'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
    $address = 'Flat No. ' . filter_var($_POST['flat'], FILTER_SANITIZE_STRING) . ', ' .
        filter_var($_POST['street'], FILTER_SANITIZE_STRING) . ', ' .
        filter_var($_POST['city'], FILTER_SANITIZE_STRING) . ', ' .
        filter_var($_POST['state'], FILTER_SANITIZE_STRING) . ', ' .
        filter_var($_POST['country'], FILTER_SANITIZE_STRING) . ' - ' .
        filter_var($_POST['pin_code'], FILTER_SANITIZE_STRING);

        if (!empty($all_items)) {
         $item_details = json_encode($all_items); // Save all item details as JSON
         $total_products = implode(', ', array_map(function ($item) {
             return $item['name'] . ' (' . $item['quantity'] . ')';
         }, $all_items));
     
         $insert_order = $conn->prepare("
             INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, item_details) 
             VALUES(?,?,?,?,?,?,?,?,?)
         ");
         $insert_order->execute([
             $user_id, $name, $number, $email, $method, $address, $total_products, $grand_total, $item_details,
         ]);

        // Update stock for all items
        foreach ($all_items as $item) {
            $product_id = $item['product_id'] ?? null; // Ensure product_id exists
            if ($product_id) {
                $quantity = $item['quantity'];
                $update_stock = $conn->prepare("UPDATE `products` SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
                $update_stock->execute([$quantity, $product_id, $quantity]);
            }
        }

        // Clear cart after checkout
        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
        $delete_cart->execute([$user_id]);

        $message[] = 'Order placed successfully!';
    } else {
        $message[] = 'Your cart or quotation is empty.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <?php include 'components/user_header.php'; ?>

    <section class="checkout-orders">
        <form action="" method="POST">
            <h3>Your Orders</h3>

            <div class="display-orders">
                <?php if (!empty($all_items)): ?>
                    <?php foreach ($all_items as $item): ?>
                        <p><?= htmlspecialchars($item['name']); ?>
                            <span>(<?= '$' . number_format($item['price'], 2); ?> x <?= htmlspecialchars($item['quantity']); ?>)</span>
                        </p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty">Your cart or quotation is empty!</p>
                <?php endif; ?>
                <input type="hidden" name="total_products" value="<?= htmlspecialchars($total_products); ?>">
                <input type="hidden" name="total_price" value="<?= htmlspecialchars($grand_total); ?>">
                <div class="grand-total">Grand Total: <span>$<?= number_format($grand_total, 2); ?></span></div>
            </div>

            <h3>Place Your Order</h3>

            <div class="flex">
                <div class="inputBox">
                    <span>Name:</span>
                    <input type="text" name="name" placeholder="Enter your name" class="box" maxlength="50" required>
                </div>
                <div class="inputBox">
                    <span>Phone Number:</span>
                    <input type="text" name="number" placeholder="Enter your phone number" class="box" maxlength="15" required>
                </div>
                <div class="inputBox">
                    <span>Email:</span>
                    <input type="email" name="email" placeholder="Enter your email" class="box" maxlength="50" required>
                </div>
                <div class="inputBox">
                    <span>Payment Method:</span>
                    <select name="method" class="box" required>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="PayPal">PayPal</option>
                    </select>
                </div>
                <div class="inputBox">
                    <span>Flat No:</span>
                    <input type="text" name="flat" placeholder="Enter your flat number" class="box" maxlength="50" required>
                </div>
                <div class="inputBox">
                    <span>Street:</span>
                    <input type="text" name="street" placeholder="Enter your street name" class="box" maxlength="50" required>
                </div>
                <div class="inputBox">
                    <span>City:</span>
                    <input type="text" name="city" placeholder="Enter your city" class="box" maxlength="50" required>
                </div>
                <div class="inputBox">
                    <span>State:</span>
                    <input type="text" name="state" placeholder="Enter your state" class="box" maxlength="50" required>
                </div>
                <div class="inputBox">
                    <span>Country:</span>
                    <input type="text" name="country" placeholder="Enter your country" class="box" maxlength="50" required>
                </div>
                <div class="inputBox">
                    <span>Pin Code:</span>
                    <input type="number" name="pin_code" placeholder="Enter your pin code" class="box" maxlength="10" required>
                </div>
            </div>

            <input type="submit" name="order" class="btn <?= ($grand_total > 0) ? '' : 'disabled'; ?>" value="Pagar ahora">

        </form>

        <form method="post" action="pago.php">
        <button>Pagar por Stripe</button>
    </form>

   </form>
    </section>

    <?php include 'components/footer.php'; ?>

    <script src="js/script.js"></script>
</body>

</html>
</html>
