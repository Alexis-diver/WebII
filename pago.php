<?php
// Incluye manualmente la biblioteca de Stripe
require __DIR__ . '/stripe-php-16.2.0/init.php';

// Configuración de Stripe
$stripe_secret_key = "sk_test_51QLgYPEOaosFAuFxxgkYR6xeRMS6In8kLwvDLu8DNY9cLqaAFbH9puR0fq9z8IwcXEyDcHghUqiljlzP0iksDWxX0008e9INIW";
\Stripe\Stripe::setApiKey($stripe_secret_key);

session_start();
$user_id = $_SESSION['user_id'] ?? '';

include 'components/connect.php';

// Manejar la creación de la sesión de Stripe y la redirección
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $checkout_session = \Stripe\Checkout\Session::create([
        "mode" => "payment",
        "success_url" => "http://localhost/success.php",
        "cancel_url" => "http://localhost/checkout.php",
        "locale" => "auto",
        "line_items" => [
            [
                "quantity" => 1,
                "price_data" => [
                    "currency" => "usd",
                    "unit_amount" => 2000, // $20.00
                    "product_data" => [
                        "name" => "T-shirt"
                    ]
                ]
            ],
            [
                "quantity" => 2,
                "price_data" => [
                    "currency" => "usd",
                    "unit_amount" => 700, // $7.00
                    "product_data" => [
                        "name" => "Hat"
                    ]
                ]
            ]
        ]
    ]);

    // Redirigir a la página de Stripe Checkout
    http_response_code(303);
    header("Location: " . $checkout_session->url);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Store</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Ruta al archivo CSS personalizado -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
    <?php include 'components/user_header.php'; ?>

    <!-- Main Section -->
    <section class="quick-view">
        <h1 class="heading">Stripe Payment Example</h1>
        <div class="box">
            <div class="row">
                <div class="content">
                    <div class="name">T-shirt</div>
                    <div class="price"><span>$</span>20.00 <span>USD</span></div>
                </div>
            </div>
            <div class="row">
                <div class="content">
                    <div class="name">Hat</div>
                    <div class="price"><span>$</span>7.00 <span>USD</span></div>
                </div>
            </div>
            <form method="post" action="">
                <button type="submit" class="btn">Pay Now</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'components/footer.php'; ?>

    <script src="script.js"></script> <!-- Ruta a tu archivo JavaScript -->
</body>
</html>
