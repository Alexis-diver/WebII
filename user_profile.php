<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<?php
$uid = $_GET['uid'];
$select_users = $conn->prepare("SELECT * FROM `users` WHERE id = ?"); 
$select_users->execute([$uid]);

while($fetch_user = $select_users->fetch(PDO::FETCH_ASSOC)){
?>

<section class="form-container">
    <form action="" method="post">
        <h3>Perfil</h3>
        <input type="name" name="name" required placeholder="enter your email" maxlength="50" class="box" value="<?= $fetch_user['name']; ?>">
        <input type="email" name="email" required placeholder="enter your email" maxlength="50" class="box" value="<?= $fetch_user['email']; ?>">
        </form>
</section>

<?php
}
      
      ?>




<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>