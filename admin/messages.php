<?php
include '../components/connect.php';

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
   $quotation_id = isset($_POST['quotation_id']) ? intval($_POST['quotation_id']) : 0;
   $new_status = isset($_POST['status']) ? $_POST['status'] : '';

   // Debugging output
   if (!$quotation_id) {
       echo "Quotation ID is missing.";
   }
   if (!$new_status) {
       echo "Status is missing.";
   }

   if ($quotation_id > 0 && in_array($new_status, ['Approved', 'Rejected'])) {
       $update_status = $conn->prepare("UPDATE `quotations` SET status = ?, reviewed_at = NOW() WHERE id = ?");
       $update_status->execute([$new_status, $quotation_id]);
       $message[] = "Quotation #{$quotation_id} has been {$new_status}.";
   } else {
       $message[] = "Invalid quotation ID or status.";
   }
}

// Fetch all quotations
$fetch_quotations = $conn->prepare("
    SELECT q.*, u.name AS user_name 
    FROM `quotations` q 
    JOIN `users` u ON q.user_id = u.id
    ORDER BY q.created_at DESC
");
$fetch_quotations->execute();
$quotations = $fetch_quotations->fetchAll(PDO::FETCH_ASSOC);

// Fetch items for a specific quotation
if (isset($_GET['quotation_id'])) {
    $quotation_id = intval($_GET['quotation_id']);
    $fetch_items = $conn->prepare("
        SELECT qi.*, p.name AS product_name, p.price AS original_price 
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
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Bandeja de mensajes</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="admin-quotations">
    <h1 class="heading">Review de Cotizaciones</h1>

    <?php if (isset($message)): ?>
        <div class="messages">
            <?php foreach ($message as $msg): ?>
                <p><?= htmlspecialchars($msg); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="quotations-container">
        <h2>Todas las cotizaciones</h2>

        <?php if (!empty($quotations)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Estatus</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quotations as $quotation): ?>
                        <tr>
                            <td><?= $quotation['id']; ?></td>
                            <td><?= htmlspecialchars($quotation['user_name']); ?></td>
                            <td><?= htmlspecialchars($quotation['status']); ?></td>
                            <td><?= htmlspecialchars($quotation['created_at']); ?></td>
                            <td>
                                <a href="messages.php?quotation_id=<?= $quotation['id']; ?>" class="btn">Ver producto</a>
                                <?php if ($quotation['status'] === 'Pending'): ?>
                                 <form method="post" action="messages.php">
                                    <input type="hidden" name="quotation_id" value="<?= $quotation['id']; ?>">
                                    <?php if ($quotation['status'] === 'Pending'): ?>

                                    <select name="status" required>
                                       <option value="" disabled selected>Actualizar estatus</option>
                                       <option value="Approved">Aprobar</option>
                                       <option value="Rejected">Rechazar</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn">Actualizar</button>
                                 </form>
                                 <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay cotizaciones</p>
        <?php endif; ?>
    </div>

    <?php if (isset($quotation_items)): ?>
        <div class="quotation-items">
            <h2>Cotizacion #<?= htmlspecialchars($quotation_id); ?> Productos</h2>

            <?php if (!empty($quotation_items)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio original</th>
                            <th>Precio requerido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quotation_items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']); ?></td>
                                <td>$<?= number_format($item['original_price'], 2); ?></td>
                                <td>$<?= number_format($item['new_price'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No se encontraron productos para esta cotizacion</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<script src="js/admin_script.js"></script>
</body>
</html>