<?php
$host = 'localhost';
$user = 'root';        
$password = '';         
$dbname = 'taskenuis';  

$conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
 
?>
