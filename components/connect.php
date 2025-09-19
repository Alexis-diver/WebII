<?php

$db_name = 'mysql:host=localhost;dbname=shop_db';
$user_name = 'root';
$user_password = '';

$conn = new PDO($db_name, $user_name, $user_password);

/*if($conn->connect_errno){
    die("Conexion Fallida". $conn->connect_errno);

}else{
    echo"conectado";
}*/
?>