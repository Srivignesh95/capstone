<?php
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    // Local server details
    $host = 'localhost';
	$db = 'eventjoin';
	$user = 'root';
	$pass = '';
} else {
    // my Hostinger server details
    $host = 'localhost';
	$db = 'u522900848_capstone';
	$user = 'u522900848_capstone';
	$pass = 'Check_Captone@321';
}
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>

