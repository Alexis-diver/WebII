<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

// Validación de inicio de sesión
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Agregar categoría
if (isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];

    if (!empty($name)) {
        $insert_category = $conn->prepare("INSERT INTO `categories` (name, description) VALUES (?, ?)");
        $insert_category->execute([$name, $description]);

        if ($insert_category) {
            $message = "Categoría añadida exitosamente.";
        } else {
            $message = "Error al añadir categoría.";
        }
    } else {
        $message = "El nombre de la categoría es obligatorio.";
    }
}

// Eliminar categoría
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_category = $conn->prepare("DELETE FROM `categories` WHERE id = ?");
    $delete_category->execute([$delete_id]);

    if ($delete_category) {
        header("Location: categories.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Categorías</title>

    <!-- font awesome cdn link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link -->
    <link rel="stylesheet" href="../css/admin_style.css">
    </head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="categories">
    <h1 class="heading">Administrar Categorías</h1>

    <!-- Formulario para agregar categoría -->
    <form action="" method="post" class="box">
        <h3>Añadir Categoría</h3>
        <input type="text" name="name" placeholder="Nombre de la categoría" required class="input-box">
        <textarea name="description" placeholder="Descripción (opcional)" class="input-box"></textarea>
        <button type="submit" name="add_category" class="btn">Agregar</button>
    </form>

    <!-- Mostrar mensaje -->
    <?php
    if (isset($message)) {
        echo "<p class='message'>$message</p>";
    }
    ?>

    <!-- Listado de categorías -->
    <div class="category-list">
        <h3>Lista de Categorías</h3>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Fecha de Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $select_categories = $conn->prepare("SELECT * FROM `categories` ORDER BY created_at DESC");
                $select_categories->execute();

                if ($select_categories->rowCount() > 0) {
                    while ($category = $select_categories->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>{$category['category_id']}</td>";
                        echo "<td>{$category['name']}</td>";
                        echo "<td>{$category['description']}</td>";
                        echo "<td>{$category['created_at']}</td>";
                        echo "<td>
                                <a href='categories.php?delete_id={$category['category_id']}' class='delete-btn' onclick='return confirm(\"¿Estás seguro de eliminar esta categoría?\")'>Eliminar</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay categorías disponibles.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</section>


<?php include '../components/footer.php'; ?>

<script src="../js/admin_script.js"></script>

</body>
</html>
