<?php
include 'components/connect.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_quotation'])) {
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $new_price = isset($_POST['new_price']) ? floatval($_POST['new_price']) : 0;

    if ($product_id <= 0 || $new_price <= 0) {
        $message[] = 'Invalid product or price!';
        header('Location: quickview.php?pid=' . $product_id);
        exit();
    }

    try {
        $conn->beginTransaction();

        $insert_quotation = $conn->prepare("INSERT INTO `quotations` (user_id, status, created_at) VALUES (?, ?, NOW())");
        $insert_quotation->execute([$user_id, 'Pending']);
        $quotation_id = $conn->lastInsertId();

        $insert_quotation_item = $conn->prepare("
            INSERT INTO `quotation_items` (quotation_id, product_id, new_price) 
            VALUES (?, ?, ?)
        ");
        $insert_quotation_item->execute([$quotation_id, $product_id, $new_price]);

        $conn->commit();

        header('Location: quotation_request.php?quotation_id=' . $quotation_id);
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $error_message = 'Error processing your request: ' . $e->getMessage();
    }
} else {
    $error_message = 'Procesando cotizacion';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Cotización</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="quotation-request">
    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <p><?= htmlspecialchars($error_message); ?></p>
                <a href="home.php" class="btn">Regresar</a>
            </div>
        <?php else: ?>
            <div class="success-message">
                <p>Tu cotizacion esta siendo procesada.</p>
                <a href="home.php" class="btn">Regresar</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>