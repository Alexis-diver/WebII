<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   // Sanitización básica para el nombre
   $name = filter_var($name, FILTER_SANITIZE_STRING);

   $email = $_POST['email'];
   // Sanitización básica para el email
   $email = filter_var($email, FILTER_SANITIZE_STRING);

   $pass = $_POST['pass']; // Obtener la contraseña sin hash para validación
   $cpass = $_POST['cpass']; // Obtener la confirmación de la contraseña sin hash para validación

   $message = []; // Inicializar array de mensajes para retroalimentación

   // --- Validación del Nombre de Usuario ---
   // Permite solo letras y espacios. No permite números ni caracteres especiales.
   if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
       $message[] = '¡El nombre de usuario solo puede contener letras y espacios!';
   }

   // --- Validación del Correo Electrónico ---
   // Valida el formato general del correo electrónico
   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
       $message[] = '¡Formato de correo electrónico inválido!';
   } else {
       // Verificar dominios específicos (@hotmail, @gmail, @outlook)
       $allowed_domains = ['hotmail.com', 'gmail.com', 'outlook.com'];
       // Extraer el dominio del correo
       $email_domain = substr(strrchr($email, "@"), 1);
       if (!in_array($email_domain, $allowed_domains)) {
           $message[] = '¡El correo electrónico debe ser de los dominios hotmail, gmail u outlook!';
       }
   }

   // --- Validación de la Contraseña ---
   // Longitud mínima de 8 caracteres
   if (strlen($pass) < 8) {
       $message[] = '¡La contraseña debe tener al menos 8 caracteres!';
   }
   // La contraseña debe incluir al menos una mayúscula, una minúscula, un número y un símbolo
   if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/", $pass)) {
       $message[] = '¡La contraseña debe incluir al menos una letra mayúscula, una minúscula, un número y un símbolo!';
   }
   // Confirmar que la contraseña y la confirmación coinciden
   if ($pass !== $cpass) {
       $message[] = '¡La confirmación de la contraseña no coincide!';
   }

   // Si no hay errores de validación, proceder con las comprobaciones de la base de datos y el registro
   if (empty($message)) {
       // Verificar si el correo electrónico ya existe en la base de datos
       $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
       $select_user->execute([$email,]);
       $row = $select_user->fetch(PDO::FETCH_ASSOC);

       if($select_user->rowCount() > 0){
           $message[] = '¡El correo electrónico ya existe!';
       } else {
           // Hashear la contraseña después de que todas las validaciones hayan pasado
           // Usando SHA1 como en tu código original. ¡Considera usar password_hash() para mayor seguridad!
           $hashed_pass = sha1($pass);

           $insert_user = $conn->prepare("INSERT INTO `users`(name, email, password) VALUES(?,?,?)");
           if ($insert_user->execute([$name, $email, $hashed_pass])) {
               $message[] = '¡Registro exitoso, por favor inicia sesión ahora!';
           } else {
               $message[] = 'El registro falló. Por favor, inténtalo de nuevo.';
           }
       }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>regístrate ahora</h3>
      <input type="text" name="name" required placeholder="ingresa tu nombre de usuario" maxlength="20"  class="box" value="<?= htmlspecialchars($name ?? '') ?>">
      <input type="email" name="email" required placeholder="ingresa tu email" maxlength="50"  class="box" oninput="this.value = this.value.replace(/\s/g, '')" value="<?= htmlspecialchars($email ?? '') ?>">
      <input type="password" name="pass" required placeholder="ingresa tu contraseña" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="cpass" required placeholder="confirma tu contraseña" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="regístrate ahora" class="btn" name="submit">
      <p>¿ya tienes una cuenta?</p>
      <a href="user_login.php" class="option-btn">inicia sesión ahora</a>
   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>