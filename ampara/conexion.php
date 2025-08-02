<?php
date_default_timezone_set('America/Lima');
$host = 'localhost';
$db   = 'u505676278_inet_ampara';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET time_zone = '-05:00';"); // Para America/Lima (UTC-5)
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
