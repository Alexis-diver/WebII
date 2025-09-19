<?php
include 'components/connect.php';
include 'components/wishlist_cart.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all approved quotations for the user
$fetch_approved_quotations = $conn->prepare("
    SELECT id, created_at 
    FROM `quotations` 
    WHERE user_id = ? AND status = 'Approved'
    ORDER BY created_at DESC
");
$fetch_approved_quotations->execute([$user_id]);
$approved_quotations = $fetch_approved_quotations->fetchAll(PDO::FETCH_ASSOC);

// Check if a specific quotation ID is passed
$quotation_id = isset($_GET['quotation_id']) ? intval($_GET['quotation_id']) : null;

// Fetch details of a specific approved quotation
$quotation_items = [];
if ($quotation_id) {
    $fetch_items = $conn->prepare("
        SELECT qi.*, p.name AS product_name, p.image_01 
        FROM `quotation_items` qi 
        JOIN `products` p ON qi.product_id = p.id 
        WHERE qi.quotation_id = ? 
    ");
    $fetch_items->execute([$quotation_id]);
    $quotation_items = $fetch_items->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Quotations</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="quotation-list">
    <h1>Approved Quotations</h1>

    <?php if (!empty($approved_quotations)): ?>
        <ul>
            <?php foreach ($approved_quotations as $quotation): ?>
                <li>
                    <a href="pendings.php?quotation_id=<?= $quotation['id']; ?>">
                        Quotation #<?= htmlspecialchars($quotation['id']); ?> - <?= htmlspecialchars($quotation['created_at']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No approved quotations found.</p>
    <?php endif; ?>
</section>

<?php if (!empty($quotation_items)): ?>
<section class="quotation-details">
    <h1>Quotation #<?= htmlspecialchars($quotation_id); ?></h1>

    <div class="products">
        <?php foreach ($quotation_items as $item): ?>
            <div class="product">
                <img src="uploaded_img/<?= htmlspecialchars($item['image_01']); ?>" alt="Product">
                <h3><?= htmlspecialchars($item['product_name']); ?></h3>
                <p>Approved Price: $<?= number_format($item['new_price'], 2); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Checkout Button -->
    <form action="checkout.php" method="get">
        <input type="hidden" name="quotation_id" value="<?= htmlspecialchars($quotation_id); ?>">
        <button type="submit" class="btn">Proceed to Checkout</button>
    </form>
</section>
<?php endif; ?>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
