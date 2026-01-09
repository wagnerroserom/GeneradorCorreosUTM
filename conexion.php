<?php
// conexion.php
// Establece la conexión con la base de datos MySQL usando XAMPP

$host = 'localhost';
$usuario = 'root';
$contrasena = ''; // Por defecto en XAMPP
$base_datos = 'estudiantes_utm';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$base_datos;charset=utf8mb4", $usuario, $contrasena);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>