<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "stockdb";

// PDO (para pagos)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión PDO: " . $e->getMessage()]);
    exit;
}

// mysqli (para stock)
$conexion = new mysqli($host, $user, $pass, $db);
if ($conexion->connect_error) {
    die("Error de conexión mysqli: " . $conexion->connect_error);
}
?>

