<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['submit'])){

   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']); // Ensure this matches the hashing used when storing passwords
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   // Call the stored function
   $stmt = $conn->prepare("SELECT authenticate_user(?, ?) AS authenticated_user_id");
   $stmt->execute([$email, $pass]);
   $result = $stmt->fetch(PDO::FETCH_ASSOC);

   if($result && $result['authenticated_user_id'] !== NULL){
     $_SESSION['user_id'] = $result['authenticated_user_id'];
     header('location:home.php');
     exit(); // Always add exit after header redirects
   }else{
     $message[] = 'incorrect username or password!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>login</title>
   
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>


<section class="form-container">

   <form action="" method="post">
     <h1>Debes crear una cuenta primero</h1>
     <h3>Inicia sesion</h3>
     <input type="email" name="email" required placeholder="Ingresa tu correo" maxlength="50"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
     <input type="password" name="pass" required placeholder="Ingresa tu contrasna" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
     <input type="submit" value="Inicia sesion" class="btn" name="submit">
     <p>No tines una cuenta?</p>
     <a href="user_register.php" class="option-btn">Crear cuenta</a>
   </form>

</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>