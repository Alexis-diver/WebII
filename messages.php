<?php
include 'components/connect.php';
session_start();

$user_id = $_SESSION['user_id'] ?? '';

if (empty($user_id)) {
    header('location:login.php');
    exit();
}

$users = [];
$error_message = '';

try {
    $select_users = $conn->prepare("SELECT id, name FROM `users` WHERE id != ?");
    $select_users->execute([$user_id]);
    $users = $select_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar usuarios: " . $e->getMessage());
    $error_message = 'Ocurrió un error al cargar la lista de usuarios.';
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        .message-selection-section {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-family: 'Inter', sans-serif;
        }

        .message-selection-section .heading {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
        }

        .message-selection-section .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 1.1rem;
            text-align: center;
            color: #fff;
        }

        .message-selection-section .message.error {
            background-color: #dc3545; 
        }

        .user-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .user-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }

        .user-card h4 {
            font-size: 1.6rem;
            color: #555;
            margin-bottom: 15px;
        }

        .user-card .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .user-card .btn:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .empty {
            text-align: center;
            padding: 20px;
            font-size: 1.2rem;
            color: #888;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        @media (max-width: 768px) {
            .message-selection-section {
                padding: 15px;
                margin: 15px;
            }

            .message-selection-section .heading {
                font-size: 2rem;
            }

            .user-list {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }

            .user-card h4 {
                font-size: 1.4rem;
            }
        }

        @media (max-width: 480px) {
            .message-selection-section {
                padding: 10px;
                margin: 10px;
            }

            .message-selection-section .heading {
                font-size: 1.8rem;
            }

            .user-list {
                grid-template-columns: 1fr; 
            }
        }
    </style>
</head>
<body>

<?php include 'components/user_social_header.php'; ?>

<section class="message-selection-section">
    <h1 class="heading">Seleccionar Usuario para Mensajes</h1>

    <?php
    if (!empty($error_message)) {
        echo '<p class="message error">' . htmlspecialchars($error_message) . '</p>';
    }
    ?>

    <div class="user-list">
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <h4><?= htmlspecialchars($user['name']) ?></h4>
                    <a href="send_message.php?receiver_id=<?= $user['id'] ?>" class="btn">Enviar Mensaje</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty">No hay otros usuarios disponibles para enviar mensajes.</p>
        <?php endif; ?>
    </div>
</section>

<?php include 'components/footer.php'; ?>

</body>
</html>
