<?php
require_once __DIR__ . '/../includes/auth.php'; 

$auth = new Auth();

// Panggil metode logout
$auth->logout();


header('Location: ../pages/login.php'); 
exit();
?>